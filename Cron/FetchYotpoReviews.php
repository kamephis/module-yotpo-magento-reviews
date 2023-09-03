<?php

namespace Kamephis\YotpoImporter\Cron;

use Kamephis\YotpoImporter\Model\ImportedReviewFactory;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Review\Model\ReviewFactory;
use Magento\Review\Model\Review;
use Magento\Catalog\Api\ProductRepositoryInterface as ProductRepository;
use Laminas\Log\Logger;
use Laminas\Log\Writer\Stream;
use Magento\Framework\App\Config\ScopeConfigInterface;

class FetchYotpoReviews
{
    protected readonly string $storeId;
    protected readonly string $perPage;
    protected readonly string $totalPages;
    protected readonly string $appKey;
    protected readonly string $authToken;

    public function __construct(
        private readonly ReviewFactory         $reviewFactory,
        private readonly ProductRepository     $productRepository,
        private readonly Curl                  $curl,
        private readonly Logger                $logger,
        private readonly ImportedReviewFactory $importedReviewFactory,
        private readonly ScopeConfigInterface  $scopeConfig
    )
    {
        // Initialize Laminas Logger with custom log file
        $writer = new Stream(BP . '/var/log/yotpo_magento_import.csv');
        $this->logger->addWriter($writer);

        $this->appKey = $this->scopeConfig->getValue('yotpo_importer/settings/app_key', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->authToken = $this->scopeConfig->getValue('yotpo_importer/settings/auth_token', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->storeId = $this->scopeConfig->getValue('yotpo_importer/settings/store_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $this->perPage = $this->scopeConfig->getValue('yotpo_importer/settings/per_page', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? 100;
        $this->totalPages = $this->scopeConfig->getValue('yotpo_importer/settings/total_pages', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ?? 1000;
    }

    public function execute(): void
    {
        try {
            $reviews = $this->fetchYotpoReviews();
            $this->logger->info('Successfully fetched Yotpo reviews.');
            $this->saveReviewsToMagento($reviews);
        } catch (\Exception $e) {
            $this->logger->err('An error occurred while fetching Yotpo reviews: ' . $e->getMessage());
        }
    }

    private function fetchYotpoReviews(): array
    {
        $baseUrl = "https://api.yotpo.com/v1/apps/{$this->appKey}/reviews?utoken={$this->authToken}";
        $allReviews = [];

        try {
            for ($page = 1; $page <= $totalPages; $page++) {
                $url = "{$baseUrl}&count={$perPage}&page={$page}";
                $this->curl->get($url);
                $response = $this->curl->getBody();

                // Log headers
                $headers = $this->curl->getHeaders();

                if ($response === false) {
                    $this->logger->err('Failed to get response from Yotpo API.');
                    break;
                }

                $responseData = json_decode($response, true);

                if (!isset($responseData['reviews']) || !is_array($responseData['reviews'])) {
                    $this->logger->warning('Received unexpected format from Yotpo API.');
                    break;
                }

                $reviews = array_map(function ($review) {
                    return [
                        'id' => $review['id'] ?? 'Unknown',
                        'sku' => $review['sku'] ?? 'Unknown',
                        'title' => $review['title'] ?? 'Unknown',
                        'name' => $review['name'] ?? 'Unknown',
                        'content' => $review['content'] ?? 'Unknown',
                    ];
                }, $responseData['reviews']);

                $allReviews = array_merge($allReviews, $reviews);
            }

        } catch (\Exception $e) {
            $this->logger->err('An exception occurred while fetching Yotpo reviews: ' . $e->getMessage());
        }

        return $allReviews;
    }


    private function saveReviewsToMagento(array $reviews): void
    {
        foreach ($reviews as $reviewData) {
            $importedReview = $this->importedReviewFactory->create();
            $importedReview->load($reviewData['id'], 'yotpo_review_id');
            if ($importedReview->getId()) {
                $this->logger->info('Skipping duplicate review for product SKU ' . $reviewData['sku']);
                continue;
            }

            try {
                $product = $this->productRepository->getById($reviewData['sku']);
                $productId = $product->getId();
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->logger->warn('Product with SKU ' . $reviewData['sku'] . ' not found. Skipping review.');
                continue;
            }

            $review = $this->reviewFactory->create();
            $review->setEntityId(1);
            $review->setEntityPkValue($productId);
            $review->setStatusId(Review::STATUS_PENDING);
            $review->setTitle($reviewData['title']);
            $review->setNickname($reviewData['name']);
            $review->setStores([$this->storeId]);
            $review->setDetail($reviewData['content']);

            try {
                $review->save();
                $this->logger->info('Successfully saved review for product SKU ' . $reviewData['sku']);
            } catch (\Exception $e) {
                $this->logger->err('Failed to save review for product SKU ' . $reviewData['sku'] . ': ' . $e->getMessage());
            }

            $importedReview->setData('yotpo_review_id', $reviewData['id']);
            $importedReview->save();
        }
    }
}
