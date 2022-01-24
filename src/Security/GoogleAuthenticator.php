<?php
namespace App\Security;

use App\Utils\Util;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class GoogleAuthenticator extends OAuth2Authenticator
{
	/**
	* @var ClientRegistry
	*/
	private $clientRegistry;
	/**
	* @var EntityManagerInterface
	*/
	private $em;
	/**
	* @var UserPasswordHasherInterface
	*/
	private $userPasswordHasher;
	/**
	* @var RouterInterface
	*/
	private $router;
	/**
	* @var SluggerInterface
	*/
	private $slugger;
	/**
	* @var ParameterBagInterface
	*/
	private $parameterbag;
	
	/**
	* FacebookAuthenticator constructor.
	* @param ClientRegistry $clientRegistry
	* @param EntityManagerInterface $em
	* @param RouterInterface $router
	* @param UserPasswordHasherInterface $userPasswordHasher
	* @param SluggerInterface $slugger
	* @param ParameterBagInterface $parameterbag
	*/
	public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em,
	RouterInterface $router,UserPasswordHasherInterface $userPasswordHasher, SluggerInterface
	$slugger,ParameterBagInterface $parameterbag)
	{
		$this->clientRegistry = $clientRegistry;
		$this->em = $em;
		$this->userPasswordHasher = $userPasswordHasher;
		$this->router = $router;
		$this->slugger=$slugger;
		$this->parameterbag=$parameterbag;
		
	}
	
	public function supports(Request $request): ?bool
	{
		// continue ONLY if the current ROUTE matches the check ROUTE
		return $request->attributes->get('_route') === 'connect_google_check';
	}
	
	public function authenticate(Request $request): Passport
	{
		$client = $this->clientRegistry->getClient('google');
		$accessToken = $this->fetchAccessToken($client);
		
		return new SelfValidatingPassport(
		new UserBadge($accessToken->getToken(), function() use ($accessToken, $client) {
			/** @var GoogleUser $googleUser */
			$googleUser = $client->fetchUserFromToken($accessToken);
			$email = $googleUser->getEmail();
			// 1) have they logged in with Facebook before? Easy!
			$existingUser = $this->em->getRepository(User::class)->findOneBy(['googleId' =>
			$googleUser->getId()]);
			if ($existingUser) {
				$user = $existingUser;
			} else {
				// 2) do we have a matching user by email?
				$user = $this->em->getRepository(User::class)->findOneBy(['email' =>
				$email]);
				if (!$user) {
					/** @var User $user */
					$user = new User();
					$user->setRoles(['ROLE_USER']);
					$user->setUserName($googleUser->getLastName());
					// $user->setLastName($googleUser->getLastName());
					// $user->setFirstName($googleUser->getFirstName());
					// $user->setEmail($googleUser->getEmail());
					
					$user->setPassword($this->userPasswordHasher->hashPassword(
						$user,
						random_bytes(10)
					)
					);
					
					$newFilename = basename ($googleUser->getAvatar()).'-
					'.uniqid().".".Util::urlMimeType($googleUser->getAvatar());
					$profileImage = file_get_contents($googleUser->getAvatar());
					$newFilename = $this->slugger->slug($newFilename);
					file_put_contents($this->parameterbag->get('avatar_directory')."//".$newFilename,$profileImage);
					// instead of its contents
					$user->setAvatar($newFilename);
				}
			}
			// 3) Maybe you just want to "register" them by creating
			// a User object
			$user->setGoogleId($googleUser->getId());
			$this->em->persist($user);
			$this->em->flush();
			return $user;
		})
	);
	}
	
	public function onAuthenticationSuccess(Request $request, TokenInterface $token, string
	$firewallName): ?Response
	{
		// change "app_homepage" to some route in your app
		$targetUrl = $this->router->generate('task_listing');
		return new RedirectResponse($targetUrl);
		// or, on success, let the request continue to be handled by the controller
		//return null;
	}
	
	public function onAuthenticationFailure(Request $request, AuthenticationException
	$exception): ?Response
	{
		$message = strtr($exception->getMessageKey(), $exception->getMessageData());
		return new Response($message, Response::HTTP_FORBIDDEN);
	}
}