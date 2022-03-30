<?php


namespace App\Api;

use App\Responses\ApiResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AuthController.
 */
class AuthController extends AbstractController
{
    /**
     * @Route ("/auth", name="api_auth",  methods={"GET"})
     *
     *
     */
    public function getTokenUser(

    )  {

        return new ApiResponse(
            ['result'=>'ok'],
            Response::HTTP_OK
        );
    }

}
