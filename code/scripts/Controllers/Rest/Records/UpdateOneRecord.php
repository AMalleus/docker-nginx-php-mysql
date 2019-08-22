<?php

namespace Controllers\Rest\Records;

use Models\CRUDInterface;
use Slim\Http\Request;
use Slim\Http\Response;

class UpdateOneRecord
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
        unset($data['id']);

        $updated = $this->model->update($args['recordId'], $data);
        if ($updated) {
            return $response->withStatus(200)->withJson([
                'success' => true,
                'affected' => $updated,
                'error' => '',
            ]);
        }

        return $response->withStatus(500)->withJson([
            'success' => false,
            'affected' => $updated,
            'error' => 'Ничего не изменилось'
        ]);
    }
}
