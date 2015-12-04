<?php
/**
 * User: Christian Augustine
 * Date: 10/3/15
 * Time: 1:51 PM
 */

namespace Min;

class Response implements \JsonSerializable
{
    const STATUS_SUCCESS = 'Success';
    const STATUS_FAILURE = 'Failure';

    const CODE_OK = 200;
    const CODE_CREATED = 201;
    const CODE_FORBIDDEN = 403;
    const CODE_NOT_FOUND = 404;
    const CODE_INTERNAL_SERVER_ERROR = 500;

    /** @var string */
    private $status = self::STATUS_SUCCESS;

    /** @var mixed */
    private $data;

    /** @var string */
    private $error = null;

    /** @var int */
    private $errorCode = null;

    /** @var array */
    private $metadata = [];

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'status'    => $this->status,
            'data'      => $this->data,
            'error'     => $this->error,
            'code'      => $this->errorCode,
            'metadata'  => $this->metadata,
            'csrfToken' => csrf_token(),
        ];
    }

    public static function jsonDeserialize($content)
    {
        $data = json_decode($content);
        $keys = array_keys(json_decode($content, true));

        $response = new Response();

        foreach ($keys as $key) {
            switch ($key) {
                case 'status':
                    $response->setStatus($data->status);
                    break;
                case 'data':
                    $response->setData($data->data);
                    break;
                case 'error':
                    $response->setErrorMessage($data->error);
                    break;
                case 'code':
                    $response->setErrorCode($data->code);
                    break;
                case 'metadata':
                    foreach ((array)$data->metadata as $key => $value) {
                        $response->setMetadata($key, $value);
                    }
                    break;
            }
        }

        return $response;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @codeCoverageIgnore
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        switch ($status) {
            case self::STATUS_SUCCESS:
                $status = $status;
                break;
            default:
                $status = self::STATUS_FAILURE;
        }

        $this->status = $status;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @codeCoverageIgnore
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @codeCoverageIgnore
     * @param \Exception $exception
     * @return $this
     */
    public function setError(\Exception $exception)
    {
        $this->error = $exception->getMessage();
        $this->errorCode = $exception->getCode();

        $this->status = self::STATUS_FAILURE;

        // clear the data, since it may be incomplete or invalid
        $this->data = null;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     * @return int
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }

    /**
     * @codeCoverageIgnore
     * @param int $errorCode
     * @return $this
     */
    public function setErrorCode($errorCode)
    {
        $this->errorCode = $errorCode;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     * @param string $errorMessage
     * @return $this
     */
    public function setErrorMessage($errorMessage)
    {
        $this->error = $errorMessage;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * @codeCoverageIgnore
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function setMetadata($key, $value)
    {
        $this->metadata[$key] = $value;

        return $this;
    }
}
