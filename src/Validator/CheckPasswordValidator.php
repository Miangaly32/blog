<?php
namespace App\Validator;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CheckPasswordValidator extends ConstraintValidator
{
    private $security;
    private $passwordHasher;

    public function __construct(Security $security,UserPasswordHasherInterface $passwordHasher)
    {
        $this->security = $security;
        $this->passwordHasher = $passwordHasher;
    }

    public function validate($value, Constraint $constraint)
    {
        if(!$this->passwordHasher->isPasswordValid($this->security->getUser(),$value))
        {
            $this->context->buildViolation($constraint->message)
                ->atPath('password')
                ->addViolation();
        }
    }
}