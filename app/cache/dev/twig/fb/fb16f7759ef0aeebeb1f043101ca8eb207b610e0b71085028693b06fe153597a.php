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

/* dinosaurs/_latestTweets.html.twig */
class __TwigTemplate_7b556501937c9f4115d6137c53603eebaefaafd1c52295a2baa6ada70f63ff35 extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<div class=\"navbar-left tweets\">
    <p class=\"text-center\">Tweets from T-Rex Problems</p>
    <p>

        ";
        // line 5
        echo ((($context["isMac"] ?? $this->getContext($context, "isMac"))) ? ("on a mac") : ("not on a mac"));
        echo "

    </p>
    <ul>
        ";
        // line 9
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["tweets"] ?? $this->getContext($context, "tweets")));
        foreach ($context['_seq'] as $context["_key"] => $context["tweet"]) {
            // line 10
            echo "            <li>";
            echo twig_escape_filter($this->env, $context["tweet"], "html", null, true);
            echo "</li>
        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['tweet'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 12
        echo "    </ul>
</div>";
    }

    public function getTemplateName()
    {
        return "dinosaurs/_latestTweets.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  56 => 12,  47 => 10,  43 => 9,  36 => 5,  30 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("<div class=\"navbar-left tweets\">
    <p class=\"text-center\">Tweets from T-Rex Problems</p>
    <p>

        {{ isMac? 'on a mac' : 'not on a mac' }}

    </p>
    <ul>
        {% for tweet in tweets %}
            <li>{{ tweet }}</li>
        {% endfor %}
    </ul>
</div>", "dinosaurs/_latestTweets.html.twig", "/home/tuanminh/projects/journey/app/Resources/views/dinosaurs/_latestTweets.html.twig");
    }
}
