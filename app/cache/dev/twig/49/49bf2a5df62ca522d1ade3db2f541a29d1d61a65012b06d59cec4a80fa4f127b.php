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

/* @WebProfiler/Collector/ajax.html.twig */
class __TwigTemplate_dece48dd4e904eabf7ba1594359f86fc284a5ca9753615b22796700f6fd19c77 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->blocks = [
            'toolbar' => [$this, 'block_toolbar'],
        ];
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "@WebProfiler/Collector/ajax.html.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_toolbar($context, array $blocks = [])
    {
        // line 4
        echo "    ";
        $context["icon"] = ('' === $tmp = "        <span>
            <img width=\"13\" height=\"28\" alt=\"AJAX requests\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAcCAYAAAB75n/uAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3gUJDAkZ7bv96AAAAP1JREFUSMftlr0NgzAQhZ8RovIQeIdUkdJFWcINFAyQJl0UpU2TAShwwxZ0kVKxAwxBRXOpQEBsfkMRiets2e/T+ex3ZkSENcPCyvH/ALs5KMsXPfZnJJz3bnKDCEoKVo2z2KM7bq252RnoxP0wRx768OKMFgFM4lXoINavxE0Qu0+0KI54vi84OE7rbE3iLQgiUlIwe2oNYm9HYc4H11WQyQCpUiYNt06X8faSN8AGAOyvl9mwas4TXE8JABAAFG6AVEk2KQOpUhYF7iizmypem52QikUwG1ivkw40p7oGQiptJmNtelSRu5Cl4tp+UB1Xt8fOEQcAtn28huIDUf6Q+fofUk0AAAAASUVORK5CYII=\">
            <span class=\"sf-toolbar-ajax-requests\">0</span>
        </span>
    ") ? '' : new Markup($tmp, $this->env->getCharset());
        // line 10
        echo "    ";
        $context["text"] = ('' === $tmp = "        <div class=\"sf-toolbar-info-piece\">
            <b>AJAX requests</b>
            <span class=\"sf-toolbar-ajax-info\"></span>
        </div>
        <div class=\"sf-toolbar-info-piece\">
            <table class=\"sf-toolbar-ajax-requests\">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>URL</th>
                        <th>Time</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody class=\"sf-toolbar-ajax-request-list\"></tbody>
            </table>
        </div>
    ") ? '' : new Markup($tmp, $this->env->getCharset());
        // line 29
        echo "    ";
        $this->loadTemplate("@WebProfiler/Profiler/toolbar_item.html.twig", "@WebProfiler/Collector/ajax.html.twig", 29)->display(twig_array_merge($context, ["link" => false]));
    }

    public function getTemplateName()
    {
        return "@WebProfiler/Collector/ajax.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  69 => 29,  49 => 10,  42 => 4,  39 => 3,  29 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("{% extends '@WebProfiler/Profiler/layout.html.twig' %}

{% block toolbar %}
    {% set icon %}
        <span>
            <img width=\"13\" height=\"28\" alt=\"AJAX requests\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAcCAYAAAB75n/uAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3gUJDAkZ7bv96AAAAP1JREFUSMftlr0NgzAQhZ8RovIQeIdUkdJFWcINFAyQJl0UpU2TAShwwxZ0kVKxAwxBRXOpQEBsfkMRiets2e/T+ex3ZkSENcPCyvH/ALs5KMsXPfZnJJz3bnKDCEoKVo2z2KM7bq252RnoxP0wRx768OKMFgFM4lXoINavxE0Qu0+0KI54vi84OE7rbE3iLQgiUlIwe2oNYm9HYc4H11WQyQCpUiYNt06X8faSN8AGAOyvl9mwas4TXE8JABAAFG6AVEk2KQOpUhYF7iizmypem52QikUwG1ivkw40p7oGQiptJmNtelSRu5Cl4tp+UB1Xt8fOEQcAtn28huIDUf6Q+fofUk0AAAAASUVORK5CYII=\">
            <span class=\"sf-toolbar-ajax-requests\">0</span>
        </span>
    {% endset %}
    {% set text %}
        <div class=\"sf-toolbar-info-piece\">
            <b>AJAX requests</b>
            <span class=\"sf-toolbar-ajax-info\"></span>
        </div>
        <div class=\"sf-toolbar-info-piece\">
            <table class=\"sf-toolbar-ajax-requests\">
                <thead>
                    <tr>
                        <th>Method</th>
                        <th>URL</th>
                        <th>Time</th>
                        <th>Profile</th>
                    </tr>
                </thead>
                <tbody class=\"sf-toolbar-ajax-request-list\"></tbody>
            </table>
        </div>
    {% endset %}
    {% include '@WebProfiler/Profiler/toolbar_item.html.twig' with { 'link': false } %}
{% endblock %}
", "@WebProfiler/Collector/ajax.html.twig", "/home/tuanminh/projects/journey/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Collector/ajax.html.twig");
    }
}
