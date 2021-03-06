<?php

namespace Controllers\Rest\Records;

use Models\CRUDInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class CreateRecord
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
        $data = $request->getParsedBody();

        $id = $this->model->insert($data);
        if ($id) {
            return $response->withStatus(200)->withJson([
                'success' => true,
                'id' => $id,
                'error' => '',
            ]);
        };

        return $response->withStatus(500)->withJson([
            'success' => false,
            'id' => $id,
            'error' => 'Ничего не изменилось'
        ]);
    }
}