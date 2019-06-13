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

/* base.html.twig */
class __TwigTemplate_b8600a36881c5d2919d469f61b33662d2e2298da453abb67ab4adb2a30758b15 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
            'title' => [$this, 'block_title'],
            'stylesheets' => [$this, 'block_stylesheets'],
            'body' => [$this, 'block_body'],
            'javascripts' => [$this, 'block_javascripts'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\" />
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <title>";
        // line 7
        $this->displayBlock('title', $context, $blocks);
        echo "</title>
        
        ";
        // line 9
        $this->displayBlock('stylesheets', $context, $blocks);
        // line 17
        echo "        <link rel=\"icon\" type=\"image/x-icon\" href=\"";
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bundle\TwigBundle\Extension\AssetsExtension')->getAssetUrl("favicon.ico"), "html", null, true);
        echo "\" />
    </head>
    <body>
        <nav class=\"navbar navbar-default navbar-fixed-top\" role=\"navigation\">
            <div class=\"container\">
                <div class=\"navbar-header\">
                    <a class=\"navbar-brand\" href=\"";
        // line 23
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("dinosaur_list");
        echo "\">
                        <img src=\"";
        // line 24
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bundle\TwigBundle\Extension\AssetsExtension')->getAssetUrl("img/dino-logo.png"), "html", null, true);
        echo "\" alt=\"Dino logo\">
                    </a>
                </div>
            </div>
        </nav>

        ";
        // line 30
        $this->displayBlock('body', $context, $blocks);
        // line 31
        echo "
        <div class=\"navbar-inverse navbar-static-bottom\" role=\"navigation\">
            <div class=\"container\">
                <div class=\"navbar-text\">
                    <p>Journey to the Center of Symfony with your guide KnpUniversity</p>
                </div>
                <div class=\"navbar-text navbar-right\">
                    <p>Dino image credit to <a href=\"http://dinosaurs.about.com/od/typesofdinosaurs/tp/dinosaurgroups.htm\" class=\"navbar-link\">about.com</a></p>
                </div>
                <div class=\"clearfix\">
                </div>

";
        // line 44
        echo "                ";
        echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\HttpKernelExtension')->renderFragment($this->env->getExtension('Symfony\Bridge\Twig\Extension\HttpKernelExtension')->controller("AppBundle:Dinosaur:_latestTweets", ["userOnMac" => $this->getAttribute($this->getAttribute($this->getAttribute(        // line 45
($context["app"] ?? $this->getContext($context, "app")), "request", []), "attributes", []), "get", [0 => "isMac"], "method")]));
        // line 46
        echo "

            </div>
        </div>

        ";
        // line 51
        $this->displayBlock('javascripts', $context, $blocks);
        // line 52
        echo "    </body>
</html>
";
    }

    // line 7
    public function block_title($context, array $blocks = [])
    {
        echo "Journey to the Center of Symfony!";
    }

    // line 9
    public function block_stylesheets($context, array $blocks = [])
    {
        // line 10
        echo "            <!-- Bootstrap -->
            <link href=\"";
        // line 11
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bundle\TwigBundle\Extension\AssetsExtension')->getAssetUrl("css/bootstrap.min.css"), "html", null, true);
        echo "\" rel=\"stylesheet\">
            <!-- Custom CSS -->
            <link href=\"";
        // line 13
        echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bundle\TwigBundle\Extension\AssetsExtension')->getAssetUrl("css/styles.css"), "html", null, true);
        echo "\" rel=\"stylesheet\">
            <!-- Google Fonts -->
            <link href='http://fonts.googleapis.com/css?family=Shadows+Into+Light' rel='stylesheet' type='text/css'>
        ";
    }

    // line 30
    public function block_body($context, array $blocks = [])
    {
    }

    // line 51
    public function block_javascripts($context, array $blocks = [])
    {
    }

    public function getTemplateName()
    {
        return "base.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  137 => 51,  132 => 30,  124 => 13,  119 => 11,  116 => 10,  113 => 9,  107 => 7,  101 => 52,  99 => 51,  92 => 46,  90 => 45,  88 => 44,  74 => 31,  72 => 30,  63 => 24,  59 => 23,  49 => 17,  47 => 9,  42 => 7,  34 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("<!DOCTYPE html>
<html>
    <head>
        <meta charset=\"UTF-8\" />
        <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
        <title>{% block title %}Journey to the Center of Symfony!{% endblock %}</title>
        
        {% block stylesheets %}
            <!-- Bootstrap -->
            <link href=\"{{ asset('css/bootstrap.min.css') }}\" rel=\"stylesheet\">
            <!-- Custom CSS -->
            <link href=\"{{ asset('css/styles.css') }}\" rel=\"stylesheet\">
            <!-- Google Fonts -->
            <link href='http://fonts.googleapis.com/css?family=Shadows+Into+Light' rel='stylesheet' type='text/css'>
        {% endblock %}
        <link rel=\"icon\" type=\"image/x-icon\" href=\"{{ asset('favicon.ico') }}\" />
    </head>
    <body>
        <nav class=\"navbar navbar-default navbar-fixed-top\" role=\"navigation\">
            <div class=\"container\">
                <div class=\"navbar-header\">
                    <a class=\"navbar-brand\" href=\"{{ path('dinosaur_list') }}\">
                        <img src=\"{{ asset('img/dino-logo.png') }}\" alt=\"Dino logo\">
                    </a>
                </div>
            </div>
        </nav>

        {% block body %}{% endblock %}

        <div class=\"navbar-inverse navbar-static-bottom\" role=\"navigation\">
            <div class=\"container\">
                <div class=\"navbar-text\">
                    <p>Journey to the Center of Symfony with your guide KnpUniversity</p>
                </div>
                <div class=\"navbar-text navbar-right\">
                    <p>Dino image credit to <a href=\"http://dinosaurs.about.com/od/typesofdinosaurs/tp/dinosaurgroups.htm\" class=\"navbar-link\">about.com</a></p>
                </div>
                <div class=\"clearfix\">
                </div>

{#                {{ render(controller('AppBundle:Dinosaur:_latestTweets')) }}#}
                {{ render(controller('AppBundle:Dinosaur:_latestTweets',{
                    'userOnMac' : app.request.attributes.get('isMac')
                })) }}

            </div>
        </div>

        {% block javascripts %}{% endblock %}
    </body>
</html>
", "base.html.twig", "/home/tuanminh/projects/journey/app/Resources/views/base.html.twig");
    }
}
