<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace api\components;

use Yii;
use yii\web\Link;
use yii\base\Model;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;
use yii\base\Arrayable;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\data\DataProviderInterface;

class QuerySerializer extends \yii\rest\Serializer
{

    public $reponseStatusCode = 200;

    public $filterColumns = [];

    /**
     * @var string the name of the query parameter containing the information about which fields should be returned
     * for a [[Model]] object. If the parameter is not provided or empty, the default set of fields as defined
     * by [[Model::fields()]] will be returned.
     */
    public $fieldsParam = 'fields';
    /**
     * @var string the name of the query parameter containing the information about which fields should be returned
     * in addition to those listed in [[fieldsParam]] for a resource object.
     */
    public $expandParam = 'expand';
    /**
     * @var string the name of the HTTP header containing the information about total number of data items.
     * This is used when serving a resource collection with pagination.
     */
    public $totalCountHeader = 'X-Pagination-Total-Count';
    /**
     * @var string the name of the HTTP header containing the information about total number of pages of data.
     * This is used when serving a resource collection with pagination.
     */
    public $pageCountHeader = 'X-Pagination-Page-Count';
    /**
     * @var string the name of the HTTP header containing the information about the current page number (1-based).
     * This is used when serving a resource collection with pagination.
     */
    public $currentPageHeader = 'X-Pagination-Current-Page';
    /**
     * @var string the name of the HTTP header containing the information about the number of data items in each page.
     * This is used when serving a resource collection with pagination.
     */
    public $perPageHeader = 'X-Pagination-Per-Page';
    /**
     * @var string the name of the envelope (e.g. `items`) for returning the resource objects in a collection.
     * This is used when serving a resource collection. When this is set and pagination is enabled, the serializer
     * will return a collection in the following format:
     *
     * ```php
     * [
     *     'items' => [...],  // assuming collectionEnvelope is "items"
     *     '_links' => {  // pagination links as returned by Pagination::getLinks()
     *         'self' => '...',
     *         'next' => '...',
     *         'last' => '...',
     *     },
     *     '_meta' => {  // meta information as returned by Pagination::toArray()
     *         'totalCount' => 100,
     *         'pageCount' => 5,
     *         'currentPage' => 1,
     *         'perPage' => 20,
     *     },
     * ]
     * ```
     *
     * If this property is not set, the resource arrays will be directly returned without using envelope.
     * The pagination information as shown in `_links` and `_meta` can be accessed from the response HTTP headers.
     */
    public $collectionEnvelope;
    /**
     * @var string the name of the envelope (e.g. `_links`) for returning the links objects.
     * It takes effect only, if `collectionEnvelope` is set.
     * @since 2.0.4
     */
    public $linksEnvelope = '_links';
    /**
     * @var string the name of the envelope (e.g. `_meta`) for returning the pagination object.
     * It takes effect only, if `collectionEnvelope` is set.
     * @since 2.0.4
     */
    public $metaEnvelope = '_meta';
    /**
     * @var Request the current request. If not set, the `request` application component will be used.
     */
    public $request;
    /**
     * @var Response the response to be sent. If not set, the `response` application component will be used.
     */
    public $response;
    /**
     * @var bool whether to preserve array keys when serializing collection data.
     * Set this to `true` to allow serialization of a collection as a JSON object where array keys are
     * used to index the model objects. The default is to serialize all collections as array, regardless
     * of how the array is indexed.
     * @see serializeDataProvider()
     * @since 2.0.10
     */
    public $preserveKeys = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->request === null) {
            $this->request = Yii::$app->getRequest();
        }
        if ($this->response === null) {
            $this->response = Yii::$app->getResponse();
        }
    }

    /**
     * Serializes the given data into a format that can be easily turned into other formats.
     * This method mainly converts the objects of recognized types into array representation.
     * It will not do conversion for unknown object types or non-object data.
     * The default implementation will handle [[Model]] and [[DataProviderInterface]].
     * You may override this method to support more object types.
     * @param mixed $data the data to be serialized.
     * @return mixed the converted data.
     */
    public function serialize($data)
    {
        $responseData = [
            'code' => $this->reponseStatusCode,
            'message' => 'success',
            'data' => [],
            'timestamp' => time(),
        ];

        if ($data instanceof Model && $data->hasErrors()) {
            return $this->serializeModelErrors($data);
        } elseif ($data instanceof Arrayable) {
            $responseData['data'] = $this->serializeModel($data);
        } elseif ($data instanceof DataProviderInterface) {
            $responseData['data'] = $this->serializeDataProvider($data);
        } else {
            $responseData['data'] = $data;
        }
        return $responseData;
    }

    /**
     * @return array the names of the requested fields. The first element is an array
     * representing the list of default fields requested, while the second element is
     * an array of the extra fields requested in addition to the default fields.
     * @see Model::fields()
     * @see Model::extraFields()
     */
    protected function getRequestedFields()
    {
        $fields = $this->request->get($this->fieldsParam);
        $expand = $this->request->get($this->expandParam);

        return [
            is_string($fields) ? preg_split('/\s*,\s*/', $fields, -1, PREG_SPLIT_NO_EMPTY) : [],
            is_string($expand) ? preg_split('/\s*,\s*/', $expand, -1, PREG_SPLIT_NO_EMPTY) : [],
        ];
    }

    /**
     * Serializes a data provider.
     * @param DataProviderInterface $dataProvider
     * @return array the array representation of the data provider.
     */
    protected function serializeDataProvider($dataProvider)
    {


        if ($this->preserveKeys) {
            $models = $dataProvider->getModels();
        } else {
            $models = array_values($dataProvider->getModels());
        }
        $models = $this->serializeModels($models);

        if (!$models) {
            throw new NotFoundHttpException("未查询到数据");
        }

        if (($pagination = $dataProvider->getPagination()) !== false) {
            $this->addPaginationHeaders($pagination);
        }
        if ($this->request->getIsHead()) {
            return null;
        } elseif ($this->collectionEnvelope === null) {
            return $models;
        } else {
            $res[$this->collectionEnvelope] = $models;
            if ($pagination == false) {
                $res = $models;
            }

            if ($pagination !== false) {
                $res = array_merge($res, $this->serializePagination($pagination));
                return $res;
            } else {
                return $res;
            }
        }
    }

    /**
     * Serializes a pagination into an array.
     * @param Pagination $pagination
     * @return array the array representation of the pagination
     * @see addPaginationHeaders()
     */
    protected function serializePagination($pagination)
    {
        return [
//            $this->linksEnvelope => Link::serialize($pagination->getLinks(true)),
            $this->metaEnvelope => [
                'totalCount' => $pagination->totalCount,
                'pageCount' => $pagination->getPageCount(),
                'currentPage' => $pagination->getPage() + 1,
                'perPage' => $pagination->getPageSize(),
            ],
        ];
    }

    /**
     * Adds HTTP headers about the pagination to the response.
     * @param Pagination $pagination
     */
    protected function addPaginationHeaders($pagination)
    {
        $links = [];
        foreach ($pagination->getLinks(true) as $rel => $url) {
            $links[] = "<$url>; rel=$rel";
        }

        $this->response->getHeaders()
            ->set($this->totalCountHeader, $pagination->totalCount)
            ->set($this->pageCountHeader, $pagination->getPageCount())
            ->set($this->currentPageHeader, $pagination->getPage() + 1)
            ->set($this->perPageHeader, $pagination->pageSize)
            ->set('Link', implode(', ', $links));
    }

    /**
     * Serializes a model object.
     * @param Arrayable $model
     * @return array the array representation of the model
     */
    protected function serializeModel($model)
    {
        if ($this->request->getIsHead()) {
            return null;
        } else {
            list ($fields, $expand) = $this->getRequestedFields();
            $data = $model->toArray($fields, $expand);
            $this->filterColumns($data);
            return $data;
        }
    }

    /**
     * Serializes the validation errors in a model.
     * @param Model $model
     * @return array the array representation of the errors
     */
    protected function serializeModelErrors($model)
    {
        $this->response->setStatusCode(422, 'Data Validation Failed.');
        $result = [];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result[] = [
                'field' => $name,
                'message' => $message,
            ];
        }

        return $result;
    }

    /**
     * Serializes a set of models.
     * @param array $models
     * @return array the array representation of the models
     */
    protected function serializeModels(array $models)
    {
        list ($fields, $expand) = $this->getRequestedFields();
        foreach ($models as $i => $model) {
            if ($model instanceof Arrayable) {
                $models[$i] = $model->toArray($fields, $expand);
            } elseif (is_array($model)) {
                $models[$i] = ArrayHelper::toArray($model);
            }
            $this->filterColumns($models[$i]);
        }
        return $models;
    }

    protected function filterColumns(&$model)
    {
        if ($this->filterColumns) {
            foreach ($this->filterColumns as $column) {
                if (isset($model[$column])) {
                    unset($model[$column]);
                }
            }
        }
    }

}
