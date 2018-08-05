<?php

namespace Controllers\Rest\Records;

use Models\CRUDInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class GetAllRecords
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
        $records = $this->model->get();

        return $response->withJson($records);
    }
}