<?php

namespace App\Controller;

use Twig\Environment;

abstract class AbstractController
{
  protected $twig;

  public function __construct(Environment $twig)
  {
    $this->twig = $twig;
  }
}
