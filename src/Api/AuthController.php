<?php


namespace App\Api;

use App\Form\SignInType;
use App\Form\SignUpType;
use App\Repository\UserRepository;
use App\Responses\ApiResponse;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AuthController.
 */
class AuthController extends AbstractController
{
    /**
     * @Route ("/sign-in", name="api_auth",  methods={"POST"})
     *
     * @SWG\Post(description="", tags={"Signin"})
     * @SWG\Parameter(name="body", in="body",description="Body json params", @Model(type=SignInType::class))
     * @SWG\Response(response=201, description="Created",
     *     examples= {
     *      "application/json":{"data":{"token":"fd886cc3-0eff-4591-a4e8-d3d0da0455ac"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     *
     */
    public function signIn(
        Request $request,
        JWTTokenManagerInterface $JWTTokenManager,
        UserRepository $userRepository,
        TranslatorInterface $translator,
        UserPasswordHasherInterface $hasher
    ): ApiResponse {
        $form = $this->createForm(SignInType::class);
        $form->submit($request->request->all());
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'] ?? null;
            $email = $email ? strtolower($email) : null;
            $user = $userRepository->getUserByEmail($email);
            if ($user && $hasher->isPasswordValid($user, $form->getData()['password'])) {
                return new ApiResponse(['token' => $JWTTokenManager->create($user)], Response::HTTP_OK);
            }
        }
        return new ApiResponse(
            [],
            Response::HTTP_FORBIDDEN,
            $translator->trans('signup.user_or_password_is_invalid', [], 'validators')
        );
    }

    /**
     * @Route("/sign-up", name="signup" ,methods={"POST"})
     *
     * @SWG\Post(description="", tags={"Signup"})
     * @SWG\Parameter(name="body", in="body",description="Body json params", @Model(type=SignUpType::class))
     * @SWG\Response(response=201, description="Created",
     *     examples= {
     *      "application/json":{"data":{"token":"fd886cc3-0eff-4591-a4e8-d3d0da0455ac"}}
     *     }
     * )
     * @SWG\Response(response=400, description="Bad Request")
     *
     * @param Request $request
     * @param UserService $userService
     * @param UserRepository $userRepository
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @param TranslatorInterface $translator
     * @return ApiResponse
     */
    public function signup(
        Request $request,
        UserService $userService,
        UserRepository $userRepository,
        JWTTokenManagerInterface $JWTTokenManager,
        TranslatorInterface $translator
    ): ApiResponse {
        $form = $this->createForm(SignUpType::class);
        $form->submit($request->request->all());
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $data['email'] = strtolower($data['email']);
            $user = $userRepository->getUserByEmail($data['email']);
            if ($user) {
                return new ApiResponse(
                    null,
                    Response::HTTP_BAD_REQUEST,
                    $translator->trans('signup.already_registered', [], 'validators')
                );
            }
            $user = $userService->addUser($data);
            return new ApiResponse(['token' => $JWTTokenManager->create($user)], Response::HTTP_CREATED);
        }
        $errors = [];
        foreach ($form->getErrors(true) as $key => $error) {
            $errors[] = ['key' => $error->getMessageParameters(), 'msg' => $error->getMessage()];
        }
        return new ApiResponse(
            $errors, Response::HTTP_BAD_REQUEST,
            $translator->trans('form.bad_request', [], 'validators')
        );
    }

}
