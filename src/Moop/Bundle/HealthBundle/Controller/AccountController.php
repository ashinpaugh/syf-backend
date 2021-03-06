<?php

namespace Moop\Bundle\HealthBundle\Controller;

use Doctrine\ORM\NonUniqueResultException;
use Moop\Bundle\HealthBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/account")
 */
class AccountController extends BaseController
{
    /**
     * @Route("")
     * @Method({"POST"})
     */
    public function createAction(Request $request)
    {
        $service = $this->container->get('moop.health.user.service');
        $valid   = $service->checkOriginalCredentials(
            $request->get('username'),
            $request->get('email'),
            $request->get('student_id')
        );
        
        if (!$valid) {
            throw new NonUniqueResultException();
        }
        
        $user   = new User();
        $school = $this->getDoctrine()->find(
            'MoopHealthBundle:School',
            $request->get('school_id')
        );
        
        $user
            ->setUsername($request->get('username'))
            ->setDisplayName($request->get('username'))
            ->setEmail($request->get('email'))
            ->setSex($request->get('sex'))
            ->setDateOfBirth($request->get('dob'))
            ->setFirstName($request->get('first_name'))
            ->setLastName($request->get('last_name'))
            ->setStudentId($request->get('student_id'))
            ->setSchool($school)
            ->setWeight($request->get('weight'))
            ->setHeight($request->get('height'))
        ;
        
        if ($group_id = $request->get('group_id')) {
            $group = $this->getRepository('MoopHealthBundle:Group')
                ->find($group_id)
            ;
            
            $group->addMember($user);
        }
        
        $service
            ->setFatOAuthTokens($user)
            ->createPasswordHash($user, $request->get('password'))
        ;
        
        $this->getDoctrine()->persist($user);
        $this->getDoctrine()->flush();
        
        return ['success' => 1];
    }
    
    /**
     * @Route("", name="account_general")
     * @Method({"GET"})
     */
    public function getAction()
    {
        /* @var User $user */
        $user = $this->getUser();
        
        return [
            'calories' => 0,
            'steps'    => 0,
            'netCal'   => 0,
        ];
    }
}