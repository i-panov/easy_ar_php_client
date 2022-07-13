<?php

namespace IPanov\EasyArClient;

use IPanov\EasyArClient\Models\Image;
use IPanov\EasyArClient\Models\Target;
use IPanov\EasyArClient\Models\UploadTargetRequest;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @link https://help.easyar.com/EasyAR%20CRS/api/README.html
 */
class EasyArClient
{
    private string $apiKey;
    private string $apiSecret;
    private string $appId;
    private HttpClientInterface $httpClient;

    public function __construct(
        string $apiKey,
        string $apiSecret,
        string $appId,
        string $appHost
    ) {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->appId = $appId;
        $this->httpClient = new NativeHttpClient(['base_uri' => $appHost]);
    }

    //-------------------------------------------------------

    public function ping(): ?string {
        return $this->request('GET', '/ping', [], 'result.message');
    }

    /** @return Target[] */
    public function all(int $pageNumber = 1, int $pageSize = 5): array {
        $params = $this->getSignedParams([
            'pageNum' => $pageNumber,
            'pageSize' => $pageSize,
        ]);

        return $this->targetRequest('GET', '/targets/infos', ['json' => $params], 'result.targets', true);
    }

    public function get(string $id): ?Target {
        $params = $this->getSignedParams();
        return $this->targetRequest('GET', "/target/$id", ['json' => $params], 'result');
    }

    public function create(UploadTargetRequest $request): ?Target {
        $params = $this->getSignedParams($request->toArray());
        return $this->targetRequest('POST', '/targets/', ['json' => $params], 'result.target');
    }

    public function update(string $id, UploadTargetRequest $request): ?Target {
        $params = $this->getSignedParams($request->toArray());
        return $this->targetRequest('PUT', "/target/$id", ['json' => $params], 'result');
    }

    public function remove(string $id): ?Target {
        $url = "/target/$id?" . http_build_query($this->getSignedParams());
        return $this->targetRequest('DELETE', $url, [], 'result');
    }

    public function detectGrade(Image $image): ?int {
        $params = $this->getSignedParams(['image' => $image->encodedContent()]);
        return $this->request('POST', '/grade/detection/', ['json' => $params], 'result.grade');
    }

    /** @return Target[] */
    public function getSimilar(Image $image): array {
        $params = $this->getSignedParams(['image' => $image->encodedContent()]);
        return $this->targetRequest('POST', '/similar/', ['json' => $params], 'result.results', true);
    }

    //-------------------------------------------------------

    private function request(string $method, string $url, array $options, string $resultPath) {
        $response = $this->httpClient->request('GET', '/ping');
        $result = $response->toArray();
        $resultPathParts = explode('.', $resultPath);

        foreach ($resultPathParts as $part) {
            if (!isset($result[$part])) {
                return null;
            }

            $result = $result[$part];
        }

        return $result;
    }

    private function getSignedParams(array $sourceParams = []): array {
        $destParams = array_merge([
            'apiKey' => $this->apiKey,
            'appId' => $this->appId,
            'timestamp' => time() * 1000, // todo: возможно умножение это ошибка - взято из примера
        ], $sourceParams);

        $destParams['signature'] = $this->getSignature($destParams);
        return $destParams;
    }

    /**
     * @link https://help.easyar.com/EasyAR%20APIKey/api/auth-signeg.html
     */
    private function getSignature(array $params): string {
        ksort($params);
        $raw = implode('', array_map(fn($value, $key) => $key . $value, $params));
        return hash('sha256', $raw . $this->apiSecret);
    }

    private function targetRequest(string $method, string $url, array $options, string $resultPath, bool $asArray = false) {
        $result = $this->request($method, $url, $options, $resultPath);

        if (!$asArray) {
            return $result ? new Target($result) : null;
        }

        return array_map(fn($data) => new Target($data), $result ?? []);
    }
}
