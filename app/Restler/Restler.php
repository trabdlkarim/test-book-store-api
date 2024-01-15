<?php

namespace App\Restler;

use Exception;
use Luracast\Restler\Restler as LuracastRestler;
use Luracast\Restler\Defaults;
use Luracast\Restler\RestException;
use Bootstrap\Container\Application;

class Restler extends LuracastRestler
{
    /**
     * App conainer instance
     * 
     * @var Application
     */
    protected $app;

    public function __construct($productionMode = false, $refreshCache = false, $container=null)
    {
        $this->app = $container ?: app();

        parent::__construct($productionMode, $refreshCache);
    }

    /**
     * Main function for processing the api request
     * and return the response
     *
     * @throws Exception     when the api service class is missing
     * @throws RestException to send error response
     */
    public function handle()
    {
        try {
            try {
                try {
                    $this->get();
                } catch (Exception $e) {
                    $this->requestData
                        = array(Defaults::$fullRequestDataName => array());
                    if (!$e instanceof RestException) {
                        $e = new RestException(
                            500,
                            $this->productionMode ? null : $e->getMessage(),
                            array(),
                            $e
                        );
                    }
                    $this->route();
                    throw $e;
                }
                if (Defaults::$useVendorMIMEVersioning)
                    $this->responseFormat = $this->negotiateResponseFormat();
                $this->route();
            } catch (Exception $e) {
                $this->negotiate();
                if (!$e instanceof RestException) {
                    $e = new RestException(
                        500,
                        $this->productionMode ? null : $e->getMessage(),
                        array(),
                        $e
                    );
                }
                throw $e;
            }
            $this->negotiate();
            $this->preAuthFilter();
            $this->authenticate();
            $this->postAuthFilter();
            $this->validate();
            $this->preCall();
            $this->call();
            $this->compose();
            $this->postCall();
            if (Defaults::$returnResponse) {
                return $this->respond();
            }
            $this->respond();
        } catch (Exception $e) {
            try {
                if (Defaults::$returnResponse) {
                    return $this->message($e);
                }
                $this->message($e);
            } catch (Exception $e2) {
                if (Defaults::$returnResponse) {
                    return $this->message($e2);
                }
                $this->message($e2);
            }
        }
    }
}