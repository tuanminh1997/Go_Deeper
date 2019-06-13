<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* dinosaurs/show.html.twig */
class __TwigTemplate_7dce64a6a76e01e81fe93f9ba0ff2299bf9da01ab6030f062f4592a523d16ee4 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->blocks = [
            'body' => [$this, 'block_body'],
        ];
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return "base.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $this->parent = $this->loadTemplate("base.html.twig", "dinosaurs/show.html.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_body($context, array $blocks = [])
    {
        // line 4
        echo "    <div class=\"dino-header text-center\">
        <h1>";
        // line 5
        echo twig_escape_filter($this->env, $this->getAttribute(($context["dino"] ?? $this->getContext($context, "dino")), "name", []), "html", null, true);
        echo " the ";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["dino"] ?? $this->getContext($context, "dino")), "type", []), "html", null, true);
        echo "</h1>
    </div>
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-xs-12\">
                <img src=\"";
        // line 10
        echo twig_escape_filter($this->env, $this->getAttribute(($context["dino"] ?? $this->getContext($context, "dino")), "imageUrl", []), "html", null, true);
        echo "\" alt=\"";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["dino"] ?? $this->getContext($context, "dino")), "type", []), "html", null, true);
        echo "\" class=\"center-block img-rounded dino-img-show\"/>
                <br>
                <a href=\"";
        // line 12
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("dinosaur_list");
        echo "\" class=\"center-block btn btn-warning btn-margin\"> < Back</a>
            </div>
        </div>
    </div>
";
    }

    public function getTemplateName()
    {
        return "dinosaurs/show.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  62 => 12,  55 => 10,  45 => 5,  42 => 4,  39 => 3,  29 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("{% extends 'base.html.twig' %}

{% block body %}
    <div class=\"dino-header text-center\">
        <h1>{{ dino.name }} the {{ dino.type }}</h1>
    </div>
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-xs-12\">
                <img src=\"{{ dino.imageUrl }}\" alt=\"{{ dino.type }}\" class=\"center-block img-rounded dino-img-show\"/>
                <br>
                <a href=\"{{ path('dinosaur_list') }}\" class=\"center-block btn btn-warning btn-margin\"> < Back</a>
            </div>
        </div>
    </div>
{% endblock %}
", "dinosaurs/show.html.twig", "/home/tuanminh/projects/journey/app/Resources/views/dinosaurs/show.html.twig");
    }
}
