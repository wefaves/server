<?php

namespace AppBundle\Controller;

use FOS\RestBundle\FOSRestBundle;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class UserController extends Controller {

    /**
     *
     * Lists all users, in the order that they signed up on Wefaves.
     *
     * ### Response ###
     *
     *      {
     *  	    "id": 1,
     *  	    "username": "Wefaves",
     *  	    "usernameCanonical": "wefaves",
     *  	    "email": "user@wefaves.com",
     *  	    "emailCanonical": "user@wefaves.com",
     *  	    "lastLogin": "2016-10-23T07:10:40+02:00",
     *  	    "roles": [
     *  	        "ROLE_USER"
     *  	    ]
     *      }
     *
     *
     * @Rest\View()
     * @Rest\Get("/users")
     *
     * @ApiDoc(
     *     resource = true,
     *     description = "Get all users",
     *     section = "Users",
     *     views = { "default", "users" },
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         204 = "Returned when no users was found"
     *     },
     *     tags = {
     *         "stable" = "#33cc33"
     *     }
     * )
     */
    public function getUsersAction(Request $request) {

        $userManager = $this->get('fos_user.user_manager');
        $users = $userManager->findUsers();

        if (empty($users))
            return new JsonResponse("There are no users in the database", Response::HTTP_NO_CONTENT);

        $res = array();

        foreach ($users as $user) {

            $res[] = array(
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "usernameCanonical" => $user->getUsernameCanonical(),
                "email" => $user->getEmail(),
                "emailCanonical" => $user->getEmailCanonical(),
                "lastLogin" => $user->getLastLogin(),
                "roles" => $user->getRoles(),
            );
        }

        return $res;
    }

    /**
     *
     * Get the authenticated user.
     *
     * ### Response ###
     *
     *      {
     *          "data": {
     *              "id": 10,
     *              "username": "Wefaves",
     *              "usernameCanonical": "wefaves",
     *              "email": "user@wefaves.com",
     *              "emailCanonical": "user@wefaves.com",
     *              "lastLogin": "2016-12-07T11:11:20+00:00",
     *              "roles": [
     *                  "ROLE_USER"
     *              ]
     *          }
     *      }
     *
     * @Rest\View()
     * @Rest\Get("/users/self")
     *
     * @ApiDoc(
     *     description = "Get the authenticated user",
     *     section = "Users",
     *     views = { "default", "users" },
     *     headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *     },
     *     input = {
     *          "class" = "FOS\UserBundle\Form\ProfileFormType"
     *     },
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         401 = "Returned when user is not authenticated",
     *         404 = "Returned when user was not found"
     *     },
     *     tags = {
     *         "stable" = "#33cc33"
     *     }
     * )
     */
    public function getUserSelfAction(Request $request) {

        /** @var $userManager UserInterface */
        $userManager = $this->get('fos_user.user_manager');

        if (empty($request->server->get("HTTP_AUTHORIZATION"))) {
            return new JsonResponse(array("message" => "You must be authenticated"), Response::HTTP_UNAUTHORIZED);
        }

        $auth_header = explode(' ', $request->server->get("HTTP_AUTHORIZATION"));
        $token = $auth_header[1];

        $data = $this->get('lexik_jwt_authentication.encoder')->decode($token);

        $user = $userManager->findUserBy(array("id" => $data["id"]));

        if (empty($user))
            return new JsonResponse(array("message" => "User not found"), Response::HTTP_NOT_FOUND);

        $res = array(
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "usernameCanonical" => $user->getUsernameCanonical(),
            "email" => $user->getEmail(),
            "emailCanonical" => $user->getEmailCanonical(),
            "lastLogin" => $user->getLastLogin(),
            "roles" => $user->getRoles(),
        );

        return $res;
    }

    /**
     *
     * Get information about a user.
     *
     * ### Response ###
     *
     *      {
     *  	    "id": 1,
     *  	    "username": "Wefaves",
     *  	    "usernameCanonical": "wefaves",
     *  	    "email": "user@wefaves.com",
     *  	    "emailCanonical": "user@wefaves.com",
     *  	    "lastLogin": "2016-10-23T07:10:40+02:00",
     *  	    "roles": [
     *  	        "ROLE_USER"
     *  	    ]
     *      }
     *
     * @Rest\View()
     * @Rest\Get("/users/{id}")
     *
     * @ApiDoc(
     *     description = "Get a single user",
     *     section = "Users",
     *     views = { "default", "users" },
     *     requirements = {
     *          { "name" = "id", "requirement" = "\d+", "dataType" = "Integer", "description" = "Unique id of user" }
     *     },
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         404 = "Returned when user was not found"
     *     },
     *     tags = {
     *         "stable" = "#33cc33"
     *     }
     * )
     */
    public function getUserAction(Request $request) {

        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array("id" => $request->get("id")));

        if (empty($user))
            return new JsonResponse(array("message" => "User not found"), Response::HTTP_NOT_FOUND);

        $res = array(
            "id" => $user->getId(),
            "username" => $user->getUsername(),
            "usernameCanonical" => $user->getUsernameCanonical(),
            "email" => $user->getEmail(),
            "emailCanonical" => $user->getEmailCanonical(),
            "lastLogin" => $user->getLastLogin(),
            "roles" => $user->getRoles(),
        );

        return $res;
    }

    /**
     *
     * Register a new user on Wefaves
     *
     * ### Request example ###
     *
     *      {
     *          "fos_user_registration_form": {
     *              "email": "user@wefaves.com",
     *              "username": "Wefaves",
     *              "plainPassword": {
     *                  "first" = "12345",
     *                  "second" = "12345
     *              }
     *          }
     *      }
     *
     * ### Response ###
     *
     *      {
     *          "data": {
     *              "id": 10,
     *              "username": "Wefaves",
     *              "usernameCanonical": "wefaves",
     *              "email": "user@wefaves.com",
     *              "emailCanonical": "user@wefaves.com",
     *              "lastLogin": {
     *                  "date": "2016-12-07 11:49:34.000000",
     *                  "timezone_type": 3,
     *                  "timezone": "UTC"
     *              },
     *              "roles": [
     *                  "ROLE_USER"
     *              ]
     *          }
     *      }
     *
     * @Rest\View()
     * @Rest\Post("/users")
     *
     * @ApiDoc(
     *     description = "Register a new user",
     *     section = "Users",
     *     views = { "default", "users" },
     *     input = {
     *          "class" = "FOS\UserBundle\Form\RegistrationFormType"
     *     },
     *     parameters = {
     *          { "name" = "email", "required" = "true", "dataType" = "String", "description" = "The email of the user" },
     *          { "name" = "username", "required" = "true", "dataType" = "String", "description" = "The username of the user" },
     *          { "name" = "plainPassword.first", "required" = "true", "dataType" = "String", "description" = "The password of the user" },
     *          { "name" = "plainPassword.second", "required" = "true", "dataType" = "String", "description" = "The password of the user. Second is here to prevent mistakes on password" }
     *     },
     *     statusCodes = {
     *         201 = "Returned when user was created",
     *         400 = "Returned on bad request"
     *     },
     *     tags = {
     *         "stable" = "#33cc33"
     *     }
     * )
     */
    public function postUsersAction(Request $request) {

        $formFactory = $this->get('fos_user.registration.form.factory');
        $userManager = $this->get('fos_user.user_manager');
        $dispatcher = $this->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new \FOS\UserBundle\Event\GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(\FOS\UserBundle\FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new \FOS\UserBundle\Event\FormEvent($form, $request);
            $dispatcher->dispatch(\FOS\UserBundle\FOSUserEvents::REGISTRATION_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_registration_confirmed');
                $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url);
            }

            $dispatcher->dispatch(\FOS\UserBundle\FOSUserEvents::REGISTRATION_COMPLETED, new \FOS\UserBundle\Event\FilterUserResponseEvent($user, $request, $response));

            $res = array(
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "usernameCanonical" => $user->getUsernameCanonical(),
                "email" => $user->getEmail(),
                "emailCanonical" => $user->getEmailCanonical(),
                "lastLogin" => $user->getLastLogin(),
                "roles" => $user->getRoles(),
            );

            $view = new JsonResponse(array('data' => $res), Response::HTTP_CREATED);

            return $view;
        }

        return $form;
    }

    /**
     *
     * Update the authenticated user. If you don't want to update a field, just let the current data.
     *
     *
     * ### Request example ###
     *
     *      {
     *          "fos_user_profile_form": {
     *              "username": "Wefaves",
     *              "email": "user@wefaves.com",
     *              "current_password": "12345",
     *          }
     *      }
     *
     * ### Response ###
     *
     *      {
     *          "data": {
     *              "id": 10,
     *              "username": "Wefaves",
     *              "usernameCanonical": "wefaves",
     *              "email": "user@wefaves.com",
     *              "emailCanonical": "user@wefaves.com",
     *              "lastLogin": "2016-12-07T11:11:20+00:00",
     *              "roles": [
     *                  "ROLE_USER"
     *              ]
     *          }
     *      }
     *
     * @Rest\View()
     * @Rest\Patch("/users/self")
     *
     * @ApiDoc(
     *     description = "Update the authenticated user",
     *     section = "Users",
     *     views = { "default", "users" },
     *     headers = {
     *          {
     *              "name" = "Authorization",
     *              "description" = "Authorization token. Value look like this: Bearer {token}",
     *              "required" = "true"
     *          }
     *     },
     *     input = {
     *          "class" = "FOS\UserBundle\Form\ProfileFormType"
     *     },
     *     parameters = {
     *          { "name" = "username", "required" = "true", "dataType" = "String", "description" = "The new username of the user" },
     *          { "name" = "email", "required" = "true", "dataType" = "String", "description" = "The new email of the user" },
     *          { "name" = "current_password", "required" = "true", "dataType" = "String", "description" = "Current password of the user to validate the update" }
     *     },
     *     statusCodes = {
     *         200 = "Returned when successful",
     *         400 = "Returned on bad request"
     *     },
     *     tags = {
     *         "stable" = "#33cc33"
     *     }
     * )
     */
    public function patchUsersAction(Request $request) {

        /** @var $userManager UserInterface */
        $userManager = $this->get('fos_user.user_manager');

        $auth_header = explode(' ', $request->server->get("HTTP_AUTHORIZATION"));
        $token = $auth_header[1];

        $data = $this->get('lexik_jwt_authentication.encoder')->decode($token);
        $user = $userManager->findUserBy(array("id" => $data["id"]));

        /** Authenticate the user */
        $token = new UsernamePasswordToken($user, $user->getPassword(), "main", $user->getRoles());
        $this->get("security.token_storage")->setToken($token);

        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

        /** @var $dispatcher EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $event = new \FOS\UserBundle\Event\GetResponseUserEvent($user, $request);

        $dispatcher->dispatch(\FOS\UserBundle\FOSUserEvents::PROFILE_EDIT_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        /** @var $formFactory FactoryInterface */
        $formFactory = $this->get('fos_user.profile.form.factory');

        $form = $formFactory->createForm(array(
                "method" => "PATCH",
        ));

        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $event = new \FOS\UserBundle\Event\FormEvent($form, $request);
            $dispatcher->dispatch(\FOS\UserBundle\FOSUserEvents::PROFILE_EDIT_SUCCESS, $event);

            $userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                $url = $this->generateUrl('fos_user_profile_show');
                $response = new RedirectResponse($url);
                $response = new \Symfony\Component\HttpFoundation\RedirectResponse($url);
            }

            $dispatcher->dispatch(\FOS\UserBundle\FOSUserEvents::PROFILE_EDIT_COMPLETED, new \FOS\UserBundle\Event\FilterUserResponseEvent($user, $request, $response));

            $res = array(
                "id" => $user->getId(),
                "username" => $user->getUsername(),
                "usernameCanonical" => $user->getUsernameCanonical(),
                "email" => $user->getEmail(),
                "emailCanonical" => $user->getEmailCanonical(),
                "lastLogin" => $user->getLastLogin(),
                "roles" => $user->getRoles(),
            );

            $view = array('data' => $res);

            return $view;
        }

        return $form;
    }

    /**
     * @Rest\View()
     * @Rest\Delete("/users/{id}")
     *
     * @ApiDoc(
     *     description = "Supprimer un utilisateur",
     *     tags = {
     *         "in-development" = "#cc0066",
     *         "stable" = "#33cc33",
     *         "beta" = "#0066ff"
     *     }
     *
     * )
     */
    /*public function deleteUsersAction(Request $request) {

        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'Unable to access this page!');

        $userManager = $this->get('fos_user.user_manager');
        $userToDelete = $userManager->findUserBy(array("id" => $request->get("id")));

        if (empty($userToDelete))
            return array("code" => Response::HTTP_NOT_FOUND);

        $userManager->deleteUser($userToDelete);

        return array("code" => Response::HTTP_OK);
    }*/
}
