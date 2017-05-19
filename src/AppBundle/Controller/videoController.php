<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\JsonResponse;
use BackendBundle\Entity\User;
use BackendBundle\Entity\Video;

class videoController extends Controller {

	public function newAction(Request $request) {
		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck == true) {
			$identity = $helpers->authCheck($hash, true);

			$json = $request->get("json", null);
			if ($json != null) {
				$params = json_decode($json);

				$createdAt = new \Datetime('now');
				$updatedAt = new \Datetime('now');
				$image = null;
				$video_path = null;

				$user_id = ($identity->sub != null) ? $identity->sub : null;
				$title = (isset($params->title)) ? $params->title : null;
				$description = (isset($params->description)) ? $params->description : null;
				$status = (isset($params->status)) ? $params->status : null;

				if ($user_id != null && $title != null) {
					$em = $this->getDoctrine()->getManager();
					$user = $em->getRepository("BackendBundle:User")->findOneBy(
							array(
								"id" => $user_id
					));
					$video = new Video();
					$video->setUser($user); //Se debe pasar todo un objeto, no un solo dato porque es una relación
					$video->setTitle($title);
					$video->setDescripcion($description);
					$video->setStatus($status);
					$video->setCreatedAt($createdAt);
					$video->setUpdatedAt($updatedAt);

					$em->persist($video);
					$em->flush();

					$video = $em->getRepository("BackendBundle:Video")->findOneBy(
							array(
								"user" => $user,
								"title" => $title,
								"status" => $status,
								"createdAt" => $createdAt
					));
					$data = array(
						"status" => "Success",
						"code" => 200,
						"data" => $video
					);
				} else {
					$data = array(
						"status" => "error",
						"code" => 400,
						"msg" => "Video not created !!"
					);
				}
			} else {
				$data = array(
					"status" => "error",
					"code" => 400,
					"msg" => "Video not created, params failed !!"
				);
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

	public function editAction(Request $request, $id = null) {
		$helpers = $this->get("app.helpers");

		$hash = $request->get("authorization", null);
		$authCheck = $helpers->authCheck($hash);

		if ($authCheck == true) {
			$identity = $helpers->authCheck($hash, true);

			$json = $request->get("json", null);
			if ($json != null) {
				$params = json_decode($json);
				
				$video_id = $id;

				$updatedAt = new \Datetime('now');
				$image = null;
				$video_path = null;

				$user_id = ($identity->sub != null) ? $identity->sub : null;
				$title = (isset($params->title)) ? $params->title : null;
				$description = (isset($params->description)) ? $params->description : null;
				$status = (isset($params->status)) ? $params->status : null;

				if ($user_id != null && $title != null) {
					$em = $this->getDoctrine()->getManager();

					$video = $em->getRepository("BackendBundle:Video")->findOneBy(
							array(
								"id" => $video_id
					));

					if (isset($identity->sub) && $identity->sub == $video->getUser()->getId()) {
						$video->setTitle($title);
						$video->setDescripcion($description);
						$video->setStatus($status);
						$video->setUpdatedAt($updatedAt);

						$em->persist($video);
						$em->flush();

						$data = array(
							"status" => "Success",
							"code" => 200,
							"msg" => "Video updated success !!"
						);
					} else {
						$data = array(
							"status" => "error",
							"code" => 400,
							"msg" => "Video updated error, you not owner"
						);
					}
				} else {
					$data = array(
						"status" => "error",
						"code" => 400,
						"msg" => "Video not updated !!"
					);
				}
			} else {
				$data = array(
					"status" => "error",
					"code" => 400,
					"msg" => "Video not created, params failed !!"
				);
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

}
