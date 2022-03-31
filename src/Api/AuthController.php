<?php


namespace App\Api;

use App\Exception\ConflictException;
use App\Exception\ForbiddenException;
use App\Form\SignInType;
use App\Form\SignUpType;
use App\Repository\UserRepository;
use App\Responses\ApiResponse;
use App\Service\UserService;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AuthController.
 */
class AuthController extends ApiController
{
    /**
     * @Route ("/sign-in", name="api_auth",  methods={"POST"})
     *
     * @SWG\Post(description="", tags={"Signin"})
     * @SWG\Parameter(name="body", in="body",description="Body json params", @Model(type=SignInType::class))
     * @SWG\Response(response=200, description="Token",
     *     examples= {
     *      "application/json":{"data":{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1Ni....gfkCyd3w"}}
     *     }
     * )
     * @SWG\Response(response=403, description="Forbidden")
     *
     */
    public function signIn(
        Request $request,
        JWTTokenManagerInterface $JWTTokenManager,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher
    ): ApiResponse {
        $data = $this->validateRequestWithClass($request, SignInType::class);
        $email = $data['email'] ?? null;
        $email = $email ? strtolower($email) : null;
        $user = $userRepository->getUserByEmail($email);
        if ($user && $hasher->isPasswordValid($user, $data['password'])) {
            return new ApiResponse(['token' => $JWTTokenManager->create($user)], Response::HTTP_OK);
        }
        throw new ForbiddenException('signup.user_or_password_is_invalid');
    }

    /**
     * @Route("/sign-up", name="signup" ,methods={"POST"})
     *
     * @SWG\Post(description="", tags={"Signup"})
     * @SWG\Parameter(name="body", in="body",description="Body json params", @Model(type=SignUpType::class))
     * @SWG\Response(response=201, description="Created",
     *     examples= {
     *      "application/json":{"data":{"token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1Ni....gfkCyd3w"}}
     *     }
     * )
     * @SWG\Response(response=400, description="Bad Request")
     *
     * @param Request $request
     * @param UserService $userService
     * @param UserRepository $userRepository
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return ApiResponse
     */
    public function signup(
        Request $request,
        UserService $userService,
        UserRepository $userRepository,
        JWTTokenManagerInterface $JWTTokenManager
    ): ApiResponse {
        $data = $this->validateRequestWithClass($request, SignUpType::class);
        $data['email'] = strtolower($data['email']);
        $user = $userRepository->getUserByEmail($data['email']);
        if ($user) {
            throw new ConflictException('signup.already_registered');
        }
        $user = $userService->addUser($data);
        return new ApiResponse(['token' => $JWTTokenManager->create($user)], Response::HTTP_CREATED);
    }

}
