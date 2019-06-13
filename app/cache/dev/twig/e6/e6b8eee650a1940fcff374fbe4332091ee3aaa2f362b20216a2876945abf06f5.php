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

/* @Debug/Profiler/dump.html.twig */
class __TwigTemplate_a2e2967f8b028b4a0288740904477fb3e3ea49dd292a820eded740f1ab58518c extends \Twig\Template
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
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "@Debug/Profiler/dump.html.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_toolbar($context, array $blocks = [])
    {
        // line 4
        echo "    ";
        $context["dumps_count"] = $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "dumpsCount", []);
        // line 5
        echo "
    ";
        // line 6
        if (($context["dumps_count"] ?? $this->getContext($context, "dumps_count"))) {
            // line 7
            echo "        ";
            ob_start(function () { return ''; });
            // line 8
            echo "            <img alt=\"dump()\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAcCAYAAACOGPReAAACcUlEQVRIx+1UPUwUYRB97+Pc8ychMSaE4E+BrYWVgCd7e5dYQSxoiIFEjYWVdNiJiVjaSGFhowUkhsLEH2Jhcvsd54nYaQ0USoAYG7WBvWOfhd+R87zFC40N00z2zTdv30xmBti3/2ZBECy3+pZJgXw+f0rSiKQBkt2SOkluSFoh+SqKoplyufylJdIgCNIAJgGMkUzXcEkAEJM0DtqUNAVgwlq7Vc9hGtR1ACiSHCeZlvQGwFClUjkKYAVAexzHFyXNAkiTvEXSury/lTqFRZI9kr4DuGKtfV6L53K5tTAMu+reZwDMkuwC8F5SUFNcr3TSEf4g6dcTuvIP139ba8vGmD5JawB6Adz9o/xMJnMSwJhLvhGG4acm/T/UCBQKhc9xHA8DAMkxx/Ob1PO8UdfDD8Vi8WnCQAw1A+fn599KegbgoOd5Izukkgbc354kjZi1di4pJumx84M7pCRPO/DdXhaD5ILz3QDAXC4XATiQ8H48DMP7jWA2mx00xrxMUB3Rjcs6gE5JZ621H/ewwsdIfgOwHoZhV22klpz883spX1Kf80v1czrnwKtJidlsdnCXnl6r5zEAUK1WpwFskjwXBMFws8SkHvq+f4HkEIDN7e3tmR3SUqm06o4DADzyff9MK2X39/efMMbU5vpBqVRabVzTCUmLJNvb2tpKu5XrFPamUqkFksfd7t9pevry+XxHHMcvSPa4Hr+W9LBarVrP836mUqkjlUqlF8B1kpcBUNKiMeZSoVD4+q97eg/Azdo9lRSTNDXvsC0AUwBuN97TXS9/HMejAAbcpnQC2JC0THIuiqLppMvfsrnN27d/2y+hkCBqr75LOwAAAABJRU5ErkJggg==\" />
            <span class=\"sf-toolbar-status sf-toolbar-status-yellow\">";
            // line 9
            echo twig_escape_filter($this->env, ($context["dumps_count"] ?? $this->getContext($context, "dumps_count")), "html", null, true);
            echo "</span>
        ";
            $context["icon"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 11
            echo "
        ";
            // line 12
            ob_start(function () { return ''; });
            // line 13
            echo "            <div class=\"sf-toolbar-info-piece\">
                <b>dump()</b>
            </div>
            ";
            // line 16
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "getDumps", [0 => "html"], "method"));
            foreach ($context['_seq'] as $context["_key"] => $context["dump"]) {
                // line 17
                echo "                <div class=\"sf-toolbar-info-piece\">
                    in
                    ";
                // line 19
                if ($this->getAttribute($context["dump"], "file", [])) {
                    // line 20
                    echo "                        ";
                    $context["link"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\CodeExtension')->getFileLink($this->getAttribute($context["dump"], "file", []), $this->getAttribute($context["dump"], "line", []));
                    // line 21
                    echo "                        ";
                    if (($context["link"] ?? $this->getContext($context, "link"))) {
                        // line 22
                        echo "                            <a href=\"";
                        echo twig_escape_filter($this->env, ($context["link"] ?? $this->getContext($context, "link")), "html", null, true);
                        echo "\" title=\"";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "file", []), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "name", []), "html", null, true);
                        echo "</a>
                        ";
                    } else {
                        // line 24
                        echo "                            <abbr title=\"";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "file", []), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "name", []), "html", null, true);
                        echo "</abbr>
                        ";
                    }
                    // line 26
                    echo "                    ";
                } else {
                    // line 27
                    echo "                        ";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "name", []), "html", null, true);
                    echo "
                    ";
                }
                // line 29
                echo "                    line ";
                echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "line", []), "html", null, true);
                echo ":
                    ";
                // line 30
                echo $this->getAttribute($context["dump"], "data", []);
                echo "
                </div>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['dump'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 33
            echo "            <img src=\"data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==\" onload=\"var h = this.parentNode.innerHTML, rx=/<script>(.*?)<\\/script>/g, s; while (s = rx.exec(h)) {eval(s[1]);};\" />
        ";
            $context["text"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
            // line 35
            echo "
        ";
            // line 36
            $this->loadTemplate("@WebProfiler/Profiler/toolbar_item.html.twig", "@Debug/Profiler/dump.html.twig", 36)->display(twig_array_merge($context, ["link" => true]));
            // line 37
            echo "    ";
        }
    }

    // line 40
    public function block_menu($context, array $blocks = [])
    {
        // line 41
        echo "    <span class=\"label\">
        <span class=\"icon\">";
        // line 43
        echo "";
        // line 44
        echo "<img alt=\"dump()\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAAcCAYAAAByDd+UAAADdUlEQVRIx71WPWwcVRD+ZneN3NzKiAgiJIrwZ9EQ2xchI+/uvb0g8SOouGtAKQAnSkHSJCZF6jSBUCQFkoNEEILGoaDCQVzeu1sLgeAI4cdyBE2qEMtE6LZA2bvbScGcWRt77+wYpntv5833vu/NzgywDVNK3VZK3d7OWRoQYC+ACgCfiEYB7JZPvzPzNQARgIvGmKt3Bej7/rjjOG8D2D8ggVqn05mJoujKlgCLxaJTKBTOENGRdT5LAH4D8KKsPwUwAWBPxoeZ+Vwcx8eazWZnfWxr/YbneSOu684T0VEB+4uZT3e73ce01k8w8+Ger9a6orV+mJn3AbjAzCkAIqKjruvOe543kstQmF0iorJc9TKA140x13s+5XL5fma+KYBrzgdBsM+yrI+J6PGexK1W67ks0zUMRcayLC/EcfxsFgwAkiRJNnufRqPxnW3bTzPzV7K1v1AonNlQ0jAMJ+TNAGCemac3egPHcdq5WVOr3bJt+yUAv+JvfY/4vj++EcPTIvGf7Xb7gDGmu1HA5eXlpG+q1mq3mPkAAAZAkun/AAZBMJZJ/VMLCwsrmwVbXFxsp2l6ME3Tg3mgxphvAHzSkzYMwycBwAEA27YrkiQJgA/6MajX6+8P+F/OAnhVYlcB/GjJwhe9I2PMH9gh01o3APTU8rNvOCrAV7DzdlXIjAIAhWGYABjKO5Gm6fP1en0+zycMw8MA3svzYebEwv9sJLe7AWA3M79jjJnZSYAwDL+UP+CG1vpBK1OUQUTj/wGpvSLn0mrSMHPUy1al1H07yC4AsEvINFYBu93unGzeQ0Sv9QtUKpWmS6XS9ACYhzIJM7cKGEXRT8z8hXw76XnerrwolmWdtyzrfB92TwF4RZaXjDG/rKmlaZqekNo34jjOh9VqddsZPDk5eS8zfwSAmDll5hP/Kt6NRuMHAGdF2hdWVlZmi8Wis1Uwz/NGhoeHP8v0xLPZWWcNi1ardVyaLgC84bru51NTUw8NCiYz0NdE5PcaRxzHM5uOGM1ms9PpdF7OgD4zNDS0pJQ6pZR6JG+qU0rN2rb9ba+EMfPldrtdWd9T84aod4nozYwPA1hi5p+JqCp7FwGMAXh00CGK+mTahDTmgcdEAG9prb+/q0E4CIIx27Yr0sZGiegBoXITwDUiiph5bpBBeLsVJJEus2W7A2Z3gfLvF5gPAAAAAElFTkSuQmCC\" />";
        // line 45
        echo "";
        // line 46
        echo "</span>
        <strong>dump()</strong>
        <span class=\"count\">
            <span>";
        // line 49
        echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "dumpsCount", []), "html", null, true);
        echo "</span>
        </span>
    </span>
";
    }

    // line 54
    public function block_panel($context, array $blocks = [])
    {
        // line 55
        echo "    <h2>dump()</h2>

    <style>
        li.sf-dump {
            list-style-type: disc;
        }
        .sf-dump ol>li {
            padding: 0;
        }
        .sf-dump a {
            cursor: pointer;
        }
        .sf-dump-compact {
            display: none;
        }
    </style>

    ";
        // line 72
        if ($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "dumpsCount", [])) {
            // line 73
            echo "        <ul class=\"alt\">
            ";
            // line 74
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "getDumps", [0 => "html"], "method"));
            foreach ($context['_seq'] as $context["_key"] => $context["dump"]) {
                // line 75
                echo "            <li class=\"sf-dump sf-reset\">
                in
                ";
                // line 77
                if ($this->getAttribute($context["dump"], "line", [])) {
                    // line 78
                    echo "                    ";
                    $context["link"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\CodeExtension')->getFileLink($this->getAttribute($context["dump"], "file", []), $this->getAttribute($context["dump"], "line", []));
                    // line 79
                    echo "                    ";
                    if (($context["link"] ?? $this->getContext($context, "link"))) {
                        // line 80
                        echo "                        <a href=\"";
                        echo twig_escape_filter($this->env, ($context["link"] ?? $this->getContext($context, "link")), "html", null, true);
                        echo "\" title=\"";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "file", []), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "name", []), "html", null, true);
                        echo "</a>
                    ";
                    } else {
                        // line 82
                        echo "                        <abbr title=\"";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "file", []), "html", null, true);
                        echo "\">";
                        echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "name", []), "html", null, true);
                        echo "</abbr>
                    ";
                    }
                    // line 84
                    echo "                ";
                } else {
                    // line 85
                    echo "                    ";
                    echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "name", []), "html", null, true);
                    echo "
                ";
                }
                // line 87
                echo "                line ";
                echo twig_escape_filter($this->env, $this->getAttribute($context["dump"], "line", []), "html", null, true);
                echo ":
                <a onclick=\"var s = this.nextElementSibling; if ('sf-dump-compact' == s.className) {this.innerHTML = '&#9660;'; s.className = 'sf-dump-expanded';} else {this.innerHTML = '&#9654;'; s.className = 'sf-dump-compact';}\">&#9654;</a>
                <span class=\"sf-dump-compact\">
                ";
                // line 90
                if ($this->getAttribute($context["dump"], "fileExcerpt", [])) {
                    echo $this->getAttribute($context["dump"], "fileExcerpt", []);
                } else {
                    echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\CodeExtension')->fileExcerpt($this->getAttribute($context["dump"], "file", []), $this->getAttribute($context["dump"], "line", []));
                }
                // line 91
                echo "                </span>

                ";
                // line 93
                echo $this->getAttribute($context["dump"], "data", []);
                echo "
            </li>
            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['dump'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 96
            echo "        </ul>
    ";
        } else {
            // line 98
            echo "        <p>
            <em>No dumped variable</em>
        </p>
    ";
        }
    }

    public function getTemplateName()
    {
        return "@Debug/Profiler/dump.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  269 => 98,  265 => 96,  256 => 93,  252 => 91,  246 => 90,  239 => 87,  233 => 85,  230 => 84,  222 => 82,  212 => 80,  209 => 79,  206 => 78,  204 => 77,  200 => 75,  196 => 74,  193 => 73,  191 => 72,  172 => 55,  169 => 54,  161 => 49,  156 => 46,  154 => 45,  152 => 44,  150 => 43,  147 => 41,  144 => 40,  139 => 37,  137 => 36,  134 => 35,  130 => 33,  121 => 30,  116 => 29,  110 => 27,  107 => 26,  99 => 24,  89 => 22,  86 => 21,  83 => 20,  81 => 19,  77 => 17,  73 => 16,  68 => 13,  66 => 12,  63 => 11,  58 => 9,  55 => 8,  52 => 7,  50 => 6,  47 => 5,  44 => 4,  41 => 3,  31 => 1,);
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
    {% set dumps_count = collector.dumpsCount %}

    {% if dumps_count %}
        {% set icon %}
            <img alt=\"dump()\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABUAAAAcCAYAAACOGPReAAACcUlEQVRIx+1UPUwUYRB97+Pc8ychMSaE4E+BrYWVgCd7e5dYQSxoiIFEjYWVdNiJiVjaSGFhowUkhsLEH2Jhcvsd54nYaQ0USoAYG7WBvWOfhd+R87zFC40N00z2zTdv30xmBti3/2ZBECy3+pZJgXw+f0rSiKQBkt2SOkluSFoh+SqKoplyufylJdIgCNIAJgGMkUzXcEkAEJM0DtqUNAVgwlq7Vc9hGtR1ACiSHCeZlvQGwFClUjkKYAVAexzHFyXNAkiTvEXSury/lTqFRZI9kr4DuGKtfV6L53K5tTAMu+reZwDMkuwC8F5SUFNcr3TSEf4g6dcTuvIP139ba8vGmD5JawB6Adz9o/xMJnMSwJhLvhGG4acm/T/UCBQKhc9xHA8DAMkxx/Ob1PO8UdfDD8Vi8WnCQAw1A+fn599KegbgoOd5Izukkgbc354kjZi1di4pJumx84M7pCRPO/DdXhaD5ILz3QDAXC4XATiQ8H48DMP7jWA2mx00xrxMUB3Rjcs6gE5JZ621H/ewwsdIfgOwHoZhV22klpz883spX1Kf80v1czrnwKtJidlsdnCXnl6r5zEAUK1WpwFskjwXBMFws8SkHvq+f4HkEIDN7e3tmR3SUqm06o4DADzyff9MK2X39/efMMbU5vpBqVRabVzTCUmLJNvb2tpKu5XrFPamUqkFksfd7t9pevry+XxHHMcvSPa4Hr+W9LBarVrP836mUqkjlUqlF8B1kpcBUNKiMeZSoVD4+q97eg/Azdo9lRSTNDXvsC0AUwBuN97TXS9/HMejAAbcpnQC2JC0THIuiqLppMvfsrnN27d/2y+hkCBqr75LOwAAAABJRU5ErkJggg==\" />
            <span class=\"sf-toolbar-status sf-toolbar-status-yellow\">{{ dumps_count }}</span>
        {% endset %}

        {% set text %}
            <div class=\"sf-toolbar-info-piece\">
                <b>dump()</b>
            </div>
            {% for dump in collector.getDumps('html') %}
                <div class=\"sf-toolbar-info-piece\">
                    in
                    {% if dump.file %}
                        {% set link = dump.file|file_link(dump.line) %}
                        {% if link %}
                            <a href=\"{{ link }}\" title=\"{{ dump.file }}\">{{ dump.name }}</a>
                        {% else %}
                            <abbr title=\"{{ dump.file }}\">{{ dump.name }}</abbr>
                        {% endif %}
                    {% else %}
                        {{ dump.name }}
                    {% endif %}
                    line {{ dump.line }}:
                    {{ dump.data|raw }}
                </div>
            {% endfor %}
            <img src=\"data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==\" onload=\"var h = this.parentNode.innerHTML, rx=/<script>(.*?)<\\/script>/g, s; while (s = rx.exec(h)) {eval(s[1]);};\" />
        {% endset %}

        {% include '@WebProfiler/Profiler/toolbar_item.html.twig' with { 'link': true } %}
    {% endif %}
{% endblock %}

{% block menu %}
    <span class=\"label\">
        <span class=\"icon\">
        {{- \"\" -}}
        <img alt=\"dump()\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAAcCAYAAAByDd+UAAADdUlEQVRIx71WPWwcVRD+ZneN3NzKiAgiJIrwZ9EQ2xchI+/uvb0g8SOouGtAKQAnSkHSJCZF6jSBUCQFkoNEEILGoaDCQVzeu1sLgeAI4cdyBE2qEMtE6LZA2bvbScGcWRt77+wYpntv5833vu/NzgywDVNK3VZK3d7OWRoQYC+ACgCfiEYB7JZPvzPzNQARgIvGmKt3Bej7/rjjOG8D2D8ggVqn05mJoujKlgCLxaJTKBTOENGRdT5LAH4D8KKsPwUwAWBPxoeZ+Vwcx8eazWZnfWxr/YbneSOu684T0VEB+4uZT3e73ce01k8w8+Ger9a6orV+mJn3AbjAzCkAIqKjruvOe543kstQmF0iorJc9TKA140x13s+5XL5fma+KYBrzgdBsM+yrI+J6PGexK1W67ks0zUMRcayLC/EcfxsFgwAkiRJNnufRqPxnW3bTzPzV7K1v1AonNlQ0jAMJ+TNAGCemac3egPHcdq5WVOr3bJt+yUAv+JvfY/4vj++EcPTIvGf7Xb7gDGmu1HA5eXlpG+q1mq3mPkAAAZAkun/AAZBMJZJ/VMLCwsrmwVbXFxsp2l6ME3Tg3mgxphvAHzSkzYMwycBwAEA27YrkiQJgA/6MajX6+8P+F/OAnhVYlcB/GjJwhe9I2PMH9gh01o3APTU8rNvOCrAV7DzdlXIjAIAhWGYABjKO5Gm6fP1en0+zycMw8MA3svzYebEwv9sJLe7AWA3M79jjJnZSYAwDL+UP+CG1vpBK1OUQUTj/wGpvSLn0mrSMHPUy1al1H07yC4AsEvINFYBu93unGzeQ0Sv9QtUKpWmS6XS9ACYhzIJM7cKGEXRT8z8hXw76XnerrwolmWdtyzrfB92TwF4RZaXjDG/rKmlaZqekNo34jjOh9VqddsZPDk5eS8zfwSAmDll5hP/Kt6NRuMHAGdF2hdWVlZmi8Wis1Uwz/NGhoeHP8v0xLPZWWcNi1ardVyaLgC84bru51NTUw8NCiYz0NdE5PcaRxzHM5uOGM1ms9PpdF7OgD4zNDS0pJQ6pZR6JG+qU0rN2rb9ba+EMfPldrtdWd9T84aod4nozYwPA1hi5p+JqCp7FwGMAXh00CGK+mTahDTmgcdEAG9prb+/q0E4CIIx27Yr0sZGiegBoXITwDUiiph5bpBBeLsVJJEus2W7A2Z3gfLvF5gPAAAAAElFTkSuQmCC\" />
        {{- \"\" -}}
        </span>
        <strong>dump()</strong>
        <span class=\"count\">
            <span>{{ collector.dumpsCount }}</span>
        </span>
    </span>
{% endblock %}

{% block panel %}
    <h2>dump()</h2>

    <style>
        li.sf-dump {
            list-style-type: disc;
        }
        .sf-dump ol>li {
            padding: 0;
        }
        .sf-dump a {
            cursor: pointer;
        }
        .sf-dump-compact {
            display: none;
        }
    </style>

    {% if collector.dumpsCount %}
        <ul class=\"alt\">
            {% for dump in collector.getDumps('html') %}
            <li class=\"sf-dump sf-reset\">
                in
                {% if dump.line %}
                    {% set link = dump.file|file_link(dump.line) %}
                    {% if link %}
                        <a href=\"{{ link }}\" title=\"{{ dump.file }}\">{{ dump.name }}</a>
                    {% else %}
                        <abbr title=\"{{ dump.file }}\">{{ dump.name }}</abbr>
                    {% endif %}
                {% else %}
                    {{ dump.name }}
                {% endif %}
                line {{ dump.line }}:
                <a onclick=\"var s = this.nextElementSibling; if ('sf-dump-compact' == s.className) {this.innerHTML = '&#9660;'; s.className = 'sf-dump-expanded';} else {this.innerHTML = '&#9654;'; s.className = 'sf-dump-compact';}\">&#9654;</a>
                <span class=\"sf-dump-compact\">
                {% if dump.fileExcerpt %}{{ dump.fileExcerpt|raw }}{% else %}{{ dump.file|file_excerpt(dump.line) }}{% endif %}
                </span>

                {{ dump.data|raw }}
            </li>
            {% endfor %}
        </ul>
    {% else %}
        <p>
            <em>No dumped variable</em>
        </p>
    {% endif %}
{% endblock %}
", "@Debug/Profiler/dump.html.twig", "/home/tuanminh/projects/journey/vendor/symfony/symfony/src/Symfony/Bundle/DebugBundle/Resources/views/Profiler/dump.html.twig");
    }
}
