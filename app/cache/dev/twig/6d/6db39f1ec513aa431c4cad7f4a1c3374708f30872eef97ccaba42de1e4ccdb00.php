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

/* @Security/Collector/security.html.twig */
class __TwigTemplate_129f4a10ba3755496685cb4f2478d5b7993be612b4364b5cfffa1b25de12ec8a extends \Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->blocks = [
            'toolbar' => [$this, 'block_toolbar'],
            'menu' => [$this, 'block_menu'],
            'panel' => [$this, 'block_panel'],
        ];
    }

    protected function doGetParent(array $context)
    {
        // line 1
        return "@WebProfiler/Profiler/layout.html.twig";
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "@Security/Collector/security.html.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_toolbar($context, array $blocks = [])
    {
        // line 4
        echo "    ";
        if ($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "tokenClass", [])) {
            // line 5
            echo "        ";
            $context["color_code"] = ((($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "enabled", []) && $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "authenticated", []))) ? ("green") : ("yellow"));
            // line 6
            echo "        ";
            $context["authentication_color_code"] = ((($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "enabled", []) && $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "authenticated", []))) ? ("green") : ("red"));
            // line 7
            echo "        ";
            $context["authentication_color_text"] = ((($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "enabled", []) && $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "authenticated", []))) ? ("Yes") : ("No"));
            // line 8
            echo "    ";
        } else {
            // line 9
            echo "        ";
            $context["color_code"] = (($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "enabled", [])) ? ("red") : ("black"));
            // line 10
            echo "    ";
        }
        // line 11
        echo "    ";
        ob_start(function () { return ''; });
        // line 12
        echo "        ";
        if ($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "tokenClass", [])) {
            // line 13
            echo "            <div class=\"sf-toolbar-info-piece\">
                <b>Logged in as</b>
                <span class=\"sf-toolbar-status sf-toolbar-status-";
            // line 15
            echo twig_escape_filter($this->env, ($context["color_code"] ?? $this->getContext($context, "color_code")), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "user", []), "html", null, true);
            echo "</span>
            </div>
            <div class=\"sf-toolbar-info-piece\">
                <b>Authenticated</b>
                <span class=\"sf-toolbar-status sf-toolbar-status-";
            // line 19
            echo twig_escape_filter($this->env, ($context["authentication_color_code"] ?? $this->getContext($context, "authentication_color_code")), "html", null, true);
            echo "\">";
            echo twig_escape_filter($this->env, ($context["authentication_color_text"] ?? $this->getContext($context, "authentication_color_text")), "html", null, true);
            echo "</span>
            </div>
            ";
            // line 21
            if (($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "tokenClass", []) != null)) {
                // line 22
                echo "            <div class=\"sf-toolbar-info-piece\">
                <b>Token class</b>
                ";
                // line 24
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\CodeExtension')->abbrClass($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "tokenClass", []));
                echo "
            </div>
            ";
            }
            // line 27
            echo "        ";
        } elseif ($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "enabled", [])) {
            // line 28
            echo "            You are not authenticated.
        ";
        } else {
            // line 30
            echo "            The security is disabled.
        ";
        }
        // line 32
        echo "    ";
        $context["text"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 33
        echo "    ";
        ob_start(function () { return ''; });
        // line 34
        echo "        <img width=\"24\" height=\"28\" alt=\"Security\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAcCAYAAAB75n/uAAAC70lEQVR42u2V3UtTYRzHu+mFwCwK+gO6CEryPlg7yiYx50vDqUwjFIZDSYUk2ZTmCysHvg9ZVggOQZiRScsR4VwXTjEwdKZWk8o6gd5UOt0mbev7g/PAkLONIOkiBx+25/v89vuc85zn2Q5Fo9F95UDwnwhS5HK5TyqVRv8m1JN6k+AiC+fn54cwbgFNIrTQ/J9IqDcJJDGBHsgDgYBSq9W6ysvLPf39/SSUUU7zsQ1yc3MjmN90OBzfRkZG1umzQqGIxPSTkIBjgdDkaGNjoza2kcFgUCE/QvMsq6io2PV6vQu1tbV8Xl7etkql2qqvr/+MbDE/Pz8s9OP2Cjhwwmw29+4R3Kec1WZnZ4fn5uamc3Jyttra2qbH8ero6JgdHh5+CvFHq9X6JZHgzODgoCVW0NPTY0N+ltU2Nzdv4GqXsYSrPp+vDw80aLFYxru6uhyQ/rDb7a8TCVJDodB1jUazTVlxcXGQ5/mbyE+z2u7u7veY38BVT3Z2djopm5qa6isrK/tQWVn5qb29fSGR4DC4PDAwMEsZHuArjGnyGKutq6v7ajQaF6urq9/MzMz0QuSemJiwQDwGkR0POhhXgILjNTU1TaWlpTxlOp1uyWQyaUjMajMzM8Nut/tJQUHBOpZppbCwkM/KytrBznuL9xDVxBMo8KXHYnu6qKjIivmrbIy67x6Px4Yd58W672ApfzY0NCyNjo7OZmRkiAv8fr+O47iwmABXtoXaG3uykF6vX7bZbF6cgZWqqiqezYkKcNtmjO+CF2AyhufgjsvlMiU7vXEF+4C4ALf9CwdrlVAqlcFkTdRqdQSHLUDgBEeSCrArAsiGwENs0XfJBE6ncxm1D8Aj/B6tigkkJSUlmxSwLYhMDeRsyyUCd+lHrWxtbe2aTCbbZTn1ZD92F0Cr8GBfgnsgDZwDt8EzMBmHMXBLqD0PDMAh9Gql3iRIESQSIAXp4CRIBZeEjIvDFZAm1J4C6UK9ROiZcvCn/+8FvwHtDdJEaRY+oQAAAABJRU5ErkJggg==\" />
        <span class=\"sf-toolbar-status sf-toolbar-status-";
        // line 35
        echo twig_escape_filter($this->env, ($context["color_code"] ?? $this->getContext($context, "color_code")), "html", null, true);
        echo "\"></span>
        ";
        // line 36
        if ($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "user", [])) {
            echo "<div class=\"sf-toolbar-status sf-toolbar-info-piece-additional\">";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "user", []), "html", null, true);
            echo "</div>";
        }
        // line 37
        echo "    ";
        $context["icon"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 38
        echo "    ";
        $this->loadTemplate("@WebProfiler/Profiler/toolbar_item.html.twig", "@Security/Collector/security.html.twig", 38)->display(twig_array_merge($context, ["link" => ($context["profiler_url"] ?? $this->getContext($context, "profiler_url"))]));
    }

    // line 41
    public function block_menu($context, array $blocks = [])
    {
        // line 42
        echo "<span class=\"label\">
    <span class=\"icon\"><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAeCAYAAABaKIzgAAADqUlEQVR42u2YXUiTYRTHs6wbg4Toru4iI7qIrgoFv3XOj0BRpoE4U5n4AV6UOg0UhhaKwvLCr2ZeTPwIsbL50Yb5Oc0hrFU2WY55sUJB2wK3qbn+J15jvJhvay9S4MWfPe95znme357znPOOHXO73f+FjkD/OdDQ0FBf5DPo6bi4OC0Wch+WaD/a11vQyxR82KJ9vQUN2QvWarUWPE9CY4xeu1yucfqkZ5Ymyd8H0BBvQcM8gqsgAdmgqJaWlgdRUVEOgUBgLy4uHpqamhLDHsHMC8h/PwihUOiuqKhwNTU12eVyuV0mkzkSEhJ2WaBhvoAKIH/Gfi45Ofmj5+KJiYnvYQ+kwmD8BGzI0tLSzfX19ZmJiQlNa2vrbG1traGnp2fObDbPNTY22vkAZQdfjY2NdbJBpqenKWV+BOsZS6qsrLSvra31ZmVlLdFzUlKSDRnZonF0dLQTV2WEfPgGvYmUu9igSL+ITpMNKhKJtm02mzI9Pd2CNNt0Ol2P1Wp9otfrB1Qqlaazs3M4LS3ts8lkGuQb9EZeXt4q6+5tOxwOIeZOskGbm5uNdXV1wzQeGBgYWlhYaKD7nZGRYYJfaUFBwWJ4ePgOxg/xucsn6BW1Wv0Ui/4Cxam8oS8AHWeDjoyMvMzOzjbjNB2wS4uKijRkl0gkeqfTmY5r5ED/dNjtdhmzVyhfoGehXJziDtOkv+P5LnSeINmg/f39zwC1kpqa+hX2O4A2kl2j0SgxV0vjhoYGPb5sH9+p99/Y2IiLiYn5CYq79w22WOjUfqBVVVU6hUIxRWnFPb4Hyfv6+sbb29ubl5eXJR0dHaMGg0GBzsFbMfnV19efEYvFtwGn25sjgJycnOdQJHxOsEGR2q2lpaXHiLNS3y0pKXm1V/2ZmZm6wsLCWap8vtpTRHV19XUs+OWgtwpAPtTU1FyCf7inPTc314gsPAKkiR0THx+/ib76Dmvv8gF6KyUlRUdjLsFvBv5JbDtOdBG9VI6qV7e1tVm6u7st8/Pzc0aj8YVUKl2h4uQDVEIt5E9AqYrhn7ffHNqSKz8/34BTny0rK3uLwvoUGRm5zdsrtKurq436HDQKjR2gYahGqVS2UJwX8h30b3UEygXK/m3K9bvTy1j+QfE26cVcIonGHHAcsfyABqNCV3+z2AUSF5i3sbQf7estaBB0H1J5VPUgVA5dZFTO2MY4xR2rYvYL8hY0gAkKhsI8dA0KJNGYsXGLOzaY2S/g6A+II1CWfgAh4q3QhpOWjAAAAABJRU5ErkJggg==\" alt=\"\" /></span>
    <strong>Security</strong>
</span>
";
    }

    // line 48
    public function block_panel($context, array $blocks = [])
    {
        // line 49
        echo "    <h2>Security</h2>
    ";
        // line 50
        if ($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "tokenClass", [])) {
            // line 51
            echo "        <table>
            <tr>
                <th>Username</th>
                <td>";
            // line 54
            echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "user", []), "html", null, true);
            echo "</td>
            </tr>
            <tr>
                <th>Authenticated?</th>
                <td>
                    ";
            // line 59
            if ($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "authenticated", [])) {
                // line 60
                echo "                        yes
                    ";
            } else {
                // line 62
                echo "                        no ";
                if ( !twig_length_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "roles", []))) {
                    echo "<em>(probably because the user has no roles)</em>";
                }
                // line 63
                echo "                    ";
            }
            // line 64
            echo "                </td>
            </tr>
            <tr>
                <th>Roles</th>
                <td>";
            // line 68
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\YamlExtension')->encode($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "roles", [])), "html", null, true);
            echo "</td>
            </tr>
            ";
            // line 70
            if (($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "tokenClass", []) != null)) {
                // line 71
                echo "            <tr>
                <th>Token class</th>
                <td>";
                // line 73
                echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "tokenClass", []), "html", null, true);
                echo "</td>
            </tr>
            ";
            }
            // line 76
            echo "        </table>
    ";
        } elseif ($this->getAttribute(        // line 77
($context["collector"] ?? $this->getContext($context, "collector")), "enabled", [])) {
            // line 78
            echo "        <p>
            <em>No token</em>
        </p>
    ";
        } else {
            // line 82
            echo "        <p>
            <em>The security component is disabled</em>
        </p>
    ";
        }
    }

    public function getTemplateName()
    {
        return "@Security/Collector/security.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  221 => 82,  215 => 78,  213 => 77,  210 => 76,  204 => 73,  200 => 71,  198 => 70,  193 => 68,  187 => 64,  184 => 63,  179 => 62,  175 => 60,  173 => 59,  165 => 54,  160 => 51,  158 => 50,  155 => 49,  152 => 48,  144 => 42,  141 => 41,  136 => 38,  133 => 37,  127 => 36,  123 => 35,  120 => 34,  117 => 33,  114 => 32,  110 => 30,  106 => 28,  103 => 27,  97 => 24,  93 => 22,  91 => 21,  84 => 19,  75 => 15,  71 => 13,  68 => 12,  65 => 11,  62 => 10,  59 => 9,  56 => 8,  53 => 7,  50 => 6,  47 => 5,  44 => 4,  41 => 3,  31 => 1,);
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
    {% if collector.tokenClass %}
        {% set color_code = (collector.enabled and collector.authenticated) ? 'green' : 'yellow'  %}
        {% set authentication_color_code = (collector.enabled and collector.authenticated) ? 'green' : 'red'  %}
        {% set authentication_color_text = (collector.enabled and collector.authenticated) ? 'Yes' : 'No'  %}
    {% else %}
        {% set color_code = collector.enabled ? 'red' : 'black'  %}
    {% endif %}
    {% set text %}
        {% if collector.tokenClass %}
            <div class=\"sf-toolbar-info-piece\">
                <b>Logged in as</b>
                <span class=\"sf-toolbar-status sf-toolbar-status-{{ color_code }}\">{{ collector.user }}</span>
            </div>
            <div class=\"sf-toolbar-info-piece\">
                <b>Authenticated</b>
                <span class=\"sf-toolbar-status sf-toolbar-status-{{ authentication_color_code }}\">{{ authentication_color_text }}</span>
            </div>
            {% if collector.tokenClass != null %}
            <div class=\"sf-toolbar-info-piece\">
                <b>Token class</b>
                {{ collector.tokenClass|abbr_class }}
            </div>
            {% endif %}
        {% elseif collector.enabled %}
            You are not authenticated.
        {% else %}
            The security is disabled.
        {% endif %}
    {% endset %}
    {% set icon %}
        <img width=\"24\" height=\"28\" alt=\"Security\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAcCAYAAAB75n/uAAAC70lEQVR42u2V3UtTYRzHu+mFwCwK+gO6CEryPlg7yiYx50vDqUwjFIZDSYUk2ZTmCysHvg9ZVggOQZiRScsR4VwXTjEwdKZWk8o6gd5UOt0mbev7g/PAkLONIOkiBx+25/v89vuc85zn2Q5Fo9F95UDwnwhS5HK5TyqVRv8m1JN6k+AiC+fn54cwbgFNIrTQ/J9IqDcJJDGBHsgDgYBSq9W6ysvLPf39/SSUUU7zsQ1yc3MjmN90OBzfRkZG1umzQqGIxPSTkIBjgdDkaGNjoza2kcFgUCE/QvMsq6io2PV6vQu1tbV8Xl7etkql2qqvr/+MbDE/Pz8s9OP2Cjhwwmw29+4R3Kec1WZnZ4fn5uamc3Jyttra2qbH8ero6JgdHh5+CvFHq9X6JZHgzODgoCVW0NPTY0N+ltU2Nzdv4GqXsYSrPp+vDw80aLFYxru6uhyQ/rDb7a8TCVJDodB1jUazTVlxcXGQ5/mbyE+z2u7u7veY38BVT3Z2djopm5qa6isrK/tQWVn5qb29fSGR4DC4PDAwMEsZHuArjGnyGKutq6v7ajQaF6urq9/MzMz0QuSemJiwQDwGkR0POhhXgILjNTU1TaWlpTxlOp1uyWQyaUjMajMzM8Nut/tJQUHBOpZppbCwkM/KytrBznuL9xDVxBMo8KXHYnu6qKjIivmrbIy67x6Px4Yd58W672ApfzY0NCyNjo7OZmRkiAv8fr+O47iwmABXtoXaG3uykF6vX7bZbF6cgZWqqiqezYkKcNtmjO+CF2AyhufgjsvlMiU7vXEF+4C4ALf9CwdrlVAqlcFkTdRqdQSHLUDgBEeSCrArAsiGwENs0XfJBE6ncxm1D8Aj/B6tigkkJSUlmxSwLYhMDeRsyyUCd+lHrWxtbe2aTCbbZTn1ZD92F0Cr8GBfgnsgDZwDt8EzMBmHMXBLqD0PDMAh9Gql3iRIESQSIAXp4CRIBZeEjIvDFZAm1J4C6UK9ROiZcvCn/+8FvwHtDdJEaRY+oQAAAABJRU5ErkJggg==\" />
        <span class=\"sf-toolbar-status sf-toolbar-status-{{ color_code }}\"></span>
        {% if collector.user %}<div class=\"sf-toolbar-status sf-toolbar-info-piece-additional\">{{ collector.user }}</div>{% endif %}
    {% endset %}
    {% include '@WebProfiler/Profiler/toolbar_item.html.twig' with { 'link': profiler_url } %}
{% endblock %}

{% block menu %}
<span class=\"label\">
    <span class=\"icon\"><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACoAAAAeCAYAAABaKIzgAAADqUlEQVR42u2YXUiTYRTHs6wbg4Toru4iI7qIrgoFv3XOj0BRpoE4U5n4AV6UOg0UhhaKwvLCr2ZeTPwIsbL50Yb5Oc0hrFU2WY55sUJB2wK3qbn+J15jvJhvay9S4MWfPe95znme357znPOOHXO73f+FjkD/OdDQ0FBf5DPo6bi4OC0Wch+WaD/a11vQyxR82KJ9vQUN2QvWarUWPE9CY4xeu1yucfqkZ5Ymyd8H0BBvQcM8gqsgAdmgqJaWlgdRUVEOgUBgLy4uHpqamhLDHsHMC8h/PwihUOiuqKhwNTU12eVyuV0mkzkSEhJ2WaBhvoAKIH/Gfi45Ofmj5+KJiYnvYQ+kwmD8BGzI0tLSzfX19ZmJiQlNa2vrbG1traGnp2fObDbPNTY22vkAZQdfjY2NdbJBpqenKWV+BOsZS6qsrLSvra31ZmVlLdFzUlKSDRnZonF0dLQTV2WEfPgGvYmUu9igSL+ITpMNKhKJtm02mzI9Pd2CNNt0Ol2P1Wp9otfrB1Qqlaazs3M4LS3ts8lkGuQb9EZeXt4q6+5tOxwOIeZOskGbm5uNdXV1wzQeGBgYWlhYaKD7nZGRYYJfaUFBwWJ4ePgOxg/xucsn6BW1Wv0Ui/4Cxam8oS8AHWeDjoyMvMzOzjbjNB2wS4uKijRkl0gkeqfTmY5r5ED/dNjtdhmzVyhfoGehXJziDtOkv+P5LnSeINmg/f39zwC1kpqa+hX2O4A2kl2j0SgxV0vjhoYGPb5sH9+p99/Y2IiLiYn5CYq79w22WOjUfqBVVVU6hUIxRWnFPb4Hyfv6+sbb29ubl5eXJR0dHaMGg0GBzsFbMfnV19efEYvFtwGn25sjgJycnOdQJHxOsEGR2q2lpaXHiLNS3y0pKXm1V/2ZmZm6wsLCWap8vtpTRHV19XUs+OWgtwpAPtTU1FyCf7inPTc314gsPAKkiR0THx+/ib76Dmvv8gF6KyUlRUdjLsFvBv5JbDtOdBG9VI6qV7e1tVm6u7st8/Pzc0aj8YVUKl2h4uQDVEIt5E9AqYrhn7ffHNqSKz8/34BTny0rK3uLwvoUGRm5zdsrtKurq436HDQKjR2gYahGqVS2UJwX8h30b3UEygXK/m3K9bvTy1j+QfE26cVcIonGHHAcsfyABqNCV3+z2AUSF5i3sbQf7estaBB0H1J5VPUgVA5dZFTO2MY4xR2rYvYL8hY0gAkKhsI8dA0KJNGYsXGLOzaY2S/g6A+II1CWfgAh4q3QhpOWjAAAAABJRU5ErkJggg==\" alt=\"\" /></span>
    <strong>Security</strong>
</span>
{% endblock %}

{% block panel %}
    <h2>Security</h2>
    {% if collector.tokenClass %}
        <table>
            <tr>
                <th>Username</th>
                <td>{{ collector.user }}</td>
            </tr>
            <tr>
                <th>Authenticated?</th>
                <td>
                    {% if collector.authenticated %}
                        yes
                    {% else %}
                        no {% if not collector.roles|length %}<em>(probably because the user has no roles)</em>{% endif %}
                    {% endif %}
                </td>
            </tr>
            <tr>
                <th>Roles</th>
                <td>{{ collector.roles|yaml_encode }}</td>
            </tr>
            {% if collector.tokenClass != null %}
            <tr>
                <th>Token class</th>
                <td>{{ collector.tokenClass }}</td>
            </tr>
            {% endif %}
        </table>
    {% elseif collector.enabled %}
        <p>
            <em>No token</em>
        </p>
    {% else %}
        <p>
            <em>The security component is disabled</em>
        </p>
    {% endif %}
{% endblock %}
", "@Security/Collector/security.html.twig", "/home/tuanminh/projects/journey/vendor/symfony/symfony/src/Symfony/Bundle/SecurityBundle/Resources/views/Collector/security.html.twig");
    }
}
