<?php

namespace App\Security;

use App\Entity\Site;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Client\Provider\GenericProvider;
use Microsoft\Graph\Graph;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Unirest\Request\Body;

class UserAuthenticator extends AbstractFormLoginAuthenticator
{
    use TargetPathTrait;

    private $entityManager;
    private $urlGenerator;
    private $csrfTokenManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->urlGenerator = $urlGenerator;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function supports(Request $request)
    {
        return 'app_login' === $request->attributes->get('_route')
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $credentials = [
            'username' => $request->request->get('username'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['username']
        );

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $userFromAD = $this->getUserFromActiveDirectory($credentials['username'], $credentials['password']);
        $user = null;
        if($userFromAD){
//        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username' => $credentials['username']]);
            $siteRepo = $this->entityManager->getRepository(Site::class);
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['idAD' => $userFromAD->getId()]);
            if(!$user) {
                $user = new User();
                $user->setEmail($userFromAD->getUserPrincipalName())
                    ->setUsername($userFromAD->getGivenName().$userFromAD->getSurname())
                    ->setName($userFromAD->getGivenName())
                    ->setFirstName($userFromAD->getSurname())
                    ->setIdAD($userFromAD->getId());

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }
            return $user;
        }

        if (!$user) {
            // fail authentication with a custom error
            throw new CustomUserMessageAuthenticationException('Vos identifiants ne sont pas corrects');
        }
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
//        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {
            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->urlGenerator->generate('home'));
       // throw new \Exception();
    }

    protected function getLoginUrl()
    {
        return $this->urlGenerator->generate('app_login');
    }

    /**
     * check if user (mail and password) is in Active Directory
     * @param $username
     * @param $password
     * @return bool|mixed
     * @throws \Microsoft\Graph\Exception\GraphException
     */
    private function getUserFromActiveDirectory($username, $password)
    {
        //params for the provider
        $dataProvider = array(
            'clientId'                => getenv('OAUTH_APP_ID'),
            'clientSecret'            => getenv('OAUTH_APP_PASSWORD'),
            'redirectUri'             => getenv('OAUTH_REDIRECT_URI'),
            'urlAuthorize'            => getenv('OAUTH_AUTHORITY').getenv('OAUTH_AUTHORIZE_ENDPOINT'),
            'urlAccessToken'          => getenv('OAUTH_AUTHORITY').getenv('OAUTH_TOKEN_ENDPOINT'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => getenv('OAUTH_SCOPES'));

        //params for the request getting the access token for the user
        $bodyRequest = array(
            'client_id'        => getenv('OAUTH_APP_ID'),
            'client_secret'    => getenv('OAUTH_APP_PASSWORD'),
            'grant_type'       => 'password',
            'username'         => $username,
            'password'         => $password,
            'scope'            => 'openid',
            'resource'         => 'https://graph.microsoft.com',
        );

        $provider = new GenericProvider($dataProvider);

        $accessTokenUrl = $provider->getBaseAccessTokenUrl($dataProvider);


        //building the POST request
        $body = Body::Form($bodyRequest);
        $headers = array('Accept' => 'application/json', 'Authorization' => 'Basic');

        $request = new \Unirest\Request();
        $request::jsonOpts(true);

        //getting the response from the request
        //200 if user is found - 400 if an error occured
        $response = $request::post($accessTokenUrl, $headers, $body);
//        dd($response);
        if($response->code == 200) {
            $accessToken = $response->body['access_token'];

            //accessing Windows Graph to get user
            $graph = new Graph();
            $graph->setAccessToken($accessToken);

            //get the current user
            $getUser = '/me';
            $user = $graph->createRequest('GET', $getUser)
                ->setReturnType(\Microsoft\Graph\Model\User::class)
                ->execute();

            return $user;
        }
        return false;
    }

}
