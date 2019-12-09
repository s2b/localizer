<?php

namespace Localizationteam\Localizer\Runner;

use Exception;
use Localizationteam\Localizer\Constants;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Requests translation status from Localizer
 *
 * @author      Peter Russ<peter.russ@4many.net>, Jo Hasenau<jh@cybercraft.de>
 * @package     TYPO3
 * @subpackage  localizer
 *
 */
class RequestStatus
{
    /**
     * @var mixed
     */
    protected $api;

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var array
     */
    protected $response = [];

    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param array $configuration
     */
    public function init(array $configuration)
    {
        switch ($configuration['type']) {
            case '0':
                if (isset($configuration['inFolder'])) {
                    if (isset($configuration['file'])) {
                        $this->path = PATH_site . trim($configuration['inFolder'], '\/') . '/' . str_replace('.xml', '',
                                $configuration['file']) . '.zip';
                    }
                }
                break;
            default:
                if (isset($configuration['token'])) {
                    if (isset($configuration['url'])) {
                        if (isset($configuration['projectKey'])) {
                            $this->api = GeneralUtility::makeInstance(
                                'Localizationteam\\' . GeneralUtility::underscoredToUpperCamelCase($configuration['type']) . '\\Api\\ApiCalls',
                                $configuration['type'],
                                $configuration['url'],
                                $configuration['workflow'],
                                $configuration['projectKey'],
                                $configuration['username'],
                                $configuration['password'],
                                ''
                            );
                            $this->api->setToken($configuration['token']);
                            if (isset($configuration['file'])) {
                                $this->path = $configuration['file'];
                            }
                        }
                    }
                }
        }
    }

    /**
     * @param array $configuration
     */
    public function run($configuration)
    {
        switch ($configuration['type']) {
            case '0':
                try {
                    if (file_exists($this->path)) {
                        $this->response['http_status_code'] = 200;
                        $this->response['files'] = [
                            [
                                'status' => Constants::API_TRANSLATION_STATUS_TRANSLATED,
                                'file'   => $this->path,
                            ],
                        ];
                    } else {
                        $this->response['http_status_code'] = 200;
                        $this->response['files'] = [
                            [
                                'status' => Constants::API_TRANSLATION_STATUS_TRANSLATED,
                                'file'   => $this->path,
                            ],
                        ];
                    }
                } catch (Exception $e) {
                    $this->response = $e->getMessage();
                }
                break;
            default:
                try {
                    $this->response = $this->api->getWorkProgress(
                        (array)$this->path
                    );
                    $this->response['http_status_code'] = '200';

                } catch (\Exception $e) {
                    $this->response = $this->api->getLastError();
                }
        }
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }
}