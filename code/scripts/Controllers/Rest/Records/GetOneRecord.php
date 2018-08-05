<?php

namespace Controllers\Rest\Records;

use Models\CRUDInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class GetOneRecord
{

    /** @var CRUDInterface */
    private $model;

    /**
     * @param CRUDInterface $model
     */
    public function __construct(CRUDInterface $model)
    {
        $this->model = $model;
    }


    public function __invoke(Request $request, Response $response, array $args)
    {
        $record = $this->model->getOne(['id' => $args['recordId']]);

        return $response->withJson($record);
    }
}