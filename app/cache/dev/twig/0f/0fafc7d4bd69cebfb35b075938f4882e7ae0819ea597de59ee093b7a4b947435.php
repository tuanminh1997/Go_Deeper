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

/* dinosaurs/index.html.twig */
class __TwigTemplate_e52ce9567f60ba3eb8178401176ce3b4413770615fd6aa7d4eb9f4941027a52b extends \Twig\Template
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
        $this->parent = $this->loadTemplate("base.html.twig", "dinosaurs/index.html.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_body($context, array $blocks = [])
    {
        // line 4
        echo "    <div class=\"container\">
        <div class=\"dino-header text-center\">
            <h1>Journey to the Center of the Earth</h1>
            <h2 class=\"journey-font\">DINOSAURS!</h2>
            ";
        // line 8
        if (($context["isMac"] ?? $this->getContext($context, "isMac"))) {
            // line 9
            echo "                <h3>AAAAAAAAAAAAAAAAAAAAAAAAAA</h3>
            ";
        }
        // line 11
        echo "        </div>
        <div class=\"row\">
            ";
        // line 13
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["dinos"] ?? $this->getContext($context, "dinos")));
        foreach ($context['_seq'] as $context["_key"] => $context["dino"]) {
            // line 14
            echo "                <div class=\"center-block col-xs-12 col-sm-6 col-md-4\">
                    <a href=\"";
            // line 15
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("dinosaur_show", ["id" => $this->getAttribute($context["dino"], "id", [])]), "html", null, true);
            echo "\">
                        <img src=\"";
            // line 16
            echo twig_escape_filter($this->env, $this->getAttribute($context["dino"], "imageUrl", []), "html", null, true);
            echo "\"  alt=\"";
            echo twig_escape_filter($this->env, $this->getAttribute($context["dino"], "type", []), "html", null, true);
            echo "\" class=\"center-block img-thumbnail dino-img\"/>
                    </a>
                    <br>
                    <a href=\"";
            // line 19
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("dinosaur_show", ["id" => $this->getAttribute($context["dino"], "id", [])]), "html", null, true);
            echo "\">
                        <p class=\"dino-name\">";
            // line 20
            echo twig_escape_filter($this->env, $this->getAttribute($context["dino"], "name", []), "html", null, true);
            echo " the ";
            echo twig_escape_filter($this->env, $this->getAttribute($context["dino"], "type", []), "html", null, true);
            echo "</p>
                    </a>
                </div>
            ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['dino'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 24
        echo "        </div>
    </div>
";
    }

    public function getTemplateName()
    {
        return "dinosaurs/index.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  93 => 24,  81 => 20,  77 => 19,  69 => 16,  65 => 15,  62 => 14,  58 => 13,  54 => 11,  50 => 9,  48 => 8,  42 => 4,  39 => 3,  29 => 1,);
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
    <div class=\"container\">
        <div class=\"dino-header text-center\">
            <h1>Journey to the Center of the Earth</h1>
            <h2 class=\"journey-font\">DINOSAURS!</h2>
            {% if isMac %}
                <h3>AAAAAAAAAAAAAAAAAAAAAAAAAA</h3>
            {% endif %}
        </div>
        <div class=\"row\">
            {% for dino in dinos %}
                <div class=\"center-block col-xs-12 col-sm-6 col-md-4\">
                    <a href=\"{{ path('dinosaur_show', {'id': dino.id}) }}\">
                        <img src=\"{{ dino.imageUrl }}\"  alt=\"{{ dino.type }}\" class=\"center-block img-thumbnail dino-img\"/>
                    </a>
                    <br>
                    <a href=\"{{ path('dinosaur_show', {'id': dino.id}) }}\">
                        <p class=\"dino-name\">{{ dino.name }} the {{ dino.type }}</p>
                    </a>
                </div>
            {% endfor %}
        </div>
    </div>
{% endblock %}
", "dinosaurs/index.html.twig", "/home/tuanminh/projects/journey/app/Resources/views/dinosaurs/index.html.twig");
    }
}
