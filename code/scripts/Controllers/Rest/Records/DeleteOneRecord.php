<?php

namespace Controllers\Rest\Records;

use Models\CRUDInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class DeleteOneRecord
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
    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function __invoke(Request $request, Response $response, array $args)
    {
        $affected = $this->model->delete(['id' => $args['recordId']]);
        if ($affected) {
            return $response->withStatus(200)->withJson([
                'success' => true,
                'affected' => $affected,
                'error' => '',
            ]);
        };

        return $response->withStatus(500)->withJson([
            'success' => false,
            'affected' => $affected,
            'error' => 'Ничего не изменилось'
        ]);
    }
}