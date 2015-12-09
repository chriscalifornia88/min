<?php

namespace Min\Controllers;

use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request;
use Min\BaseModel;
use Min\Exceptions\ErrorCode;
use Illuminate\Routing\Controller as BaseController;
use Min\Response;

abstract class RestController extends BaseController
{
    protected static $model = BaseModel::class;

    /** @var Response */
    protected $response;

    public function __construct()
    {
        $this->response = new Response();
    }

    /**
     * @return \Illuminate\Http\Response
     */
    protected function createResponse()
    {
        $httpCode = Response::CODE_OK;

        if (!is_null($this->response->getErrorCode())) {
            switch ($this->response->getErrorCode()) {
                case ErrorCode::ACCESS_DENIED:
                    $httpCode = Response::CODE_FORBIDDEN;
                    break;
                case ErrorCode::MODEL_NOT_FOUND:
                    $httpCode = Response::CODE_NOT_FOUND;
                    break;
                default:
                    $httpCode = Response::CODE_INTERNAL_SERVER_ERROR;
            }
        }

        return response()->json($this->response, $httpCode);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $model = $this->getModelByUri($request);

            /** @var Paginator $paginator */
            if (is_object($model)) {
                $paginator = $model->paginate();
            } else {
                $paginator = $model::paginate();
            }
            
            $paginationData = $paginator->toArray();

            $this->response->setData($paginationData['data']);
            unset($paginationData['data']);
            $this->response->setMetadata('pagination', $paginationData);
        } catch (\Exception $ex) {
            $this->response->setError($ex);
        }

        return $this->createResponse();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        try {
            $model = $this->getModelByUri($request);

            $this->response->setData($model);
        } catch (\Exception $ex) {
            $this->response->setError($ex);
        }

        return $this->createResponse();
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $model = $this->getModelByUri($request);

            /** @var BaseModel $instance */
            if (is_object($model)) {
                $instance = $model->create($request->input());
            } else {
                $instance = $model::create($request->input());
            }

            $this->response->setData($instance);
        } catch (\Exception $ex) {
            $this->response->setError($ex);
        }

        return $this->createResponse();
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            /** @var BaseModel $model */
            $model = $this->getModelByUri($request);
            
            $model->update($request->input());

            $this->response->setData($model);
        } catch (\Exception $ex) {
            $this->response->setError($ex);
        }

        return $this->createResponse();
    }

    /**
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        try {
            /** @var BaseModel $model */
            $model = $this->getModelByUri($request);

            $model->delete();

        } catch (\Exception $ex) {
            $this->response->setError($ex);
        }

        return $this->createResponse();
    }

    /**
     * @param Request $request
     * @return BaseModel $model|\Illuminate\Database\Eloquent\Relations\Relation
     */
    private function getModelByUri(Request $request)
    {
        $uriParts = explode('?', $request->getRequestUri());
        $parts = explode('/', current($uriParts));
        array_shift($parts);

        /** @var BaseModel $baseModel */
        $baseModel = '\App\\' . ucfirst(array_shift($parts));

        if (count($parts) === 0) {
            return $baseModel;
        }

        $instance = $baseModel::fetch(array_shift($parts));

        // Traverse the URI parts as the baseModel's relationships
        $relationships = [];
        $relationship = 0;
        $isId = false;
        foreach ($parts as $part) {
            if ($isId) {
                $relationships[$relationship] = $part;
                $isId = false;
            } else {
                $relationship = $part;
                $relationships[$relationship] = null;
                $isId = true;
            }
        }

        foreach ($relationships as $relationship => $id) {
            $instance = $instance->fetchRelationship($relationship, $id);
        }

        return $instance;
    }
}
