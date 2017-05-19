<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\User;

class UserController extends Controller {

	public function newAction(Request $request) {
		$helpers = $this->get("app.helpers");

		$json = $request->get("json", null);
		$params = json_decode($json);
		$data = array(
			"status" => "error",
			"code" => 400,
			"msg" => "User not created"
		);
		if ($json != null) {
			$createdAt = new \Datetime("now");
			$image = null;
			$role = "user";
			$email = (isset($params->email)) ? $params->email : null;
			$name = (isset($params->name) && preg_match('/^[\p{Latin}\s]+$/u', $params->name)) ? $params->name : null;
			$surname = (isset($params->surname) && preg_match('/^[\p{Latin}\s]+$/u', $params->surname)) ? $params->surname : null;
			$password = (isset($params->password)) ? $params->password : null;

			$emailConstraint = new Assert\Email();
			$emailConstraint->message = "This email is not valid";
			$validate_email = $this->get("validator")->validate($email, $emailConstraint);
			if ($email != null && count($validate_email) == 0 && $password != null && strlen(trim($name)) > 0 && strlen(trim($surname)) > 0) {
				$user = new User();
				$user->setCratedAt($createdAt);
				$user->setEmail($email);
				$user->setRole($role);
				$user->setImage($image);
				$user->setName($name);
				$user->setSurname($surname);

				//Cifrar contraseña
				$pwd = hash('sha256', $password);
				$user->setPassword($pwd);

				$em = $this->getDoctrine()->getManager();
				$isset_user = $em->getRepository("BackendBundle:User")->findBy(
						array(
							"email" => $email
				));
				if (count($isset_user) == 0) {
					$em->persist($user);
					$em->flush(); //crea el registro en la BD
					$data["status"] = 'Success';
					$data["code"] = 200;
					$data["msg"] = 'New User Created!!';
				} else {
					$data = array(
						"status" => "error",
						"code" => 400,
						"msg" => "User not created, Duplicated!!"
					);
				}
			}
		}
		return $helpers->json($data);
	}

	public function editAction(Request $request) {
		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck == true) {

			$identity = $helpers->authCheck($hash, true);

			$em = $this->getDoctrine()->getManager();
			$user = $em->getRepository("BackendBundle:User")->findOneBy(
					array(
						"id" => $identity->sub
			));

			$json = $request->get("json", null);
			$params = json_decode($json);
			$data = array(
				"status" => "error",
				"code" => 400,
				"msg" => "User not created"
			);
			if ($json != null) {
				$createdAt = new \Datetime("now");
				$image = null;
				$role = "user";
				$email = (isset($params->email)) ? $params->email : null;
				$name = (isset($params->name) && preg_match('/^[\p{Latin}\s]+$/u', $params->name)) ? $params->name : null;
				$surname = (isset($params->surname) && preg_match('/^[\p{Latin}\s]+$/u', $params->surname)) ? $params->surname : null;
				$password = (isset($params->password)) ? $params->password : null;

				$emailConstraint = new Assert\Email();
				$emailConstraint->message = "This email is not valid";
				$validate_email = $this->get("validator")->validate($email, $emailConstraint);
				if ($email != null && count($validate_email) == 0 && strlen(trim($name)) > 0 && strlen(trim($surname)) > 0) {
					$user->setCratedAt($createdAt);
					$user->setEmail($email);
					$user->setRole($role);
					$user->setImage($image);
					$user->setName($name);
					$user->setSurname($surname);

					if ($password != null) {
						//Cifrar contraseña
						$pwd = hash('sha256', $password);
						$user->setPassword($pwd);
					}

					$em = $this->getDoctrine()->getManager();
					$isset_user = $em->getRepository("BackendBundle:User")->findBy(
							array(
								"email" => $email
					));
					if (count($isset_user) == 0 || $identity->email == $email) {
						$em->persist($user);
						$em->flush(); //crea el registro en la BD
						$data["status"] = 'Success';
						$data["code"] = 200;
						$data["msg"] = 'User Updated!!';
					} else {
						$data = array(
							"status" => "error",
							"code" => 400,
							"msg" => "User not Updated !!"
						);
					}
				}
			}
		} else {
			$data = array(
				"status" => "error",
				"code" => 400,
				"msg" => "Authorization incorrect !!"
			);
		}
		return $helpers->json($data);
	}

	public function uploadImageAction(Request $request) {
		$helpers = $this->get("app.helpers");

		$hash = $request->get("Authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck) {
			$identity = $helpers->authCheck($hash, true);

			$em = $this->getDoctrine()->getManager();
			$user = $em->getRepository("BackendBundle:User")->findOneBy(
					array(
						"id" => $identity->sub
			));
			//upload file
			$file = $request->files->get("image");

			if (!empty($file) && $file != null) {
				$ext = $file->guessExtension();
				if ($ext == "jpeg" || $ext == "jpg" || $ext == "png" || $ext == "gif") {
					$file_name = time() . "." . $ext;
					$file->move("uploads/users", $file_name);
					$user->setImage($file_name);

					$em->persist($user);
					$em->flush();

					$data = array(
						"status" => "Success",
						"code" => 200,
						"msg" => "Image uploaded!!"
					);
				} else {
					$data = array(
						"status" => "Error!!",
						"code" => 400,
						"msg" => "Extension invalid!!"
					);
				}
			} else {
				$data = array(
					"status" => "error",
					"code" => 400,
					"msg" => "Image not upladed"
				);
			}
		} else {
			$data = array(
				"status" => "error conche",
				"code" => 400,
				"msg" => "Authorization not valid"
			);
		}
		return $helpers->json($data);
	}

}
