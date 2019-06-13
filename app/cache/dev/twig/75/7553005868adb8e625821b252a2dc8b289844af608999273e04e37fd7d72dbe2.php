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

/* @WebProfiler/Collector/request.html.twig */
class __TwigTemplate_9d4ace5c2f719e6a3c3b7c45c4909fab2380822dbbdd6d979e094536f8c24268 extends \Twig\Template
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
        $this->parent = $this->loadTemplate("@WebProfiler/Profiler/layout.html.twig", "@WebProfiler/Collector/request.html.twig", 1);
        $this->parent->display($context, array_merge($this->blocks, $blocks));
    }

    // line 3
    public function block_toolbar($context, array $blocks = [])
    {
        // line 4
        echo "    ";
        ob_start(function () { return ''; });
        // line 5
        echo "        ";
        if ($this->getAttribute($this->getAttribute(($context["collector"] ?? null), "controller", [], "any", false, true), "class", [], "any", true, true)) {
            // line 6
            echo "            ";
            $context["link"] = $this->env->getExtension('Symfony\Bridge\Twig\Extension\CodeExtension')->getFileLink($this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "controller", []), "file", []), $this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "controller", []), "line", []));
            // line 7
            echo "            ";
            if ($this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "controller", []), "method", [])) {
                // line 8
                echo "                <span class=\"sf-toolbar-info-class sf-toolbar-info-with-next-pointer\">";
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\CodeExtension')->abbrClass($this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "controller", []), "class", []));
                echo "</span>
                <span class=\"sf-toolbar-info-method\"";
                // line 9
                if (($context["link"] ?? $this->getContext($context, "link"))) {
                    echo " onclick=\"window.location='";
                    echo twig_escape_filter($this->env, twig_escape_filter($this->env, ($context["link"] ?? $this->getContext($context, "link")), "js"), "html", null, true);
                    echo "';window.event.stopPropagation();return false;\"";
                }
                echo ">
                    ";
                // line 10
                echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "controller", []), "method", []), "html", null, true);
                echo "
                </span>
            ";
            } else {
                // line 13
                echo "                <span class=\"sf-toolbar-info-class\"";
                if (($context["link"] ?? $this->getContext($context, "link"))) {
                    echo " onclick=\"window.location='";
                    echo twig_escape_filter($this->env, twig_escape_filter($this->env, ($context["link"] ?? $this->getContext($context, "link")), "js"), "html", null, true);
                    echo "';window.event.stopPropagation();return false;\"";
                }
                echo ">";
                echo $this->env->getExtension('Symfony\Bridge\Twig\Extension\CodeExtension')->abbrClass($this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "controller", []), "class", []));
                echo "</span>
            ";
            }
            // line 15
            echo "        ";
        } else {
            // line 16
            echo "            <span class=\"sf-toolbar-info-class\">";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "controller", []), "html", null, true);
            echo "</span>
        ";
        }
        // line 18
        echo "    ";
        $context["request_handler"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 19
        echo "    ";
        $context["request_status_code_color"] = (((400 > $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "statuscode", []))) ? ((((200 == $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "statuscode", []))) ? ("green") : ("yellow"))) : ("red"));
        // line 20
        echo "    ";
        $context["request_route"] = (($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "route", [])) ? ($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "route", [])) : ("NONE"));
        // line 21
        echo "    ";
        ob_start(function () { return ''; });
        // line 22
        echo "        <img width=\"28\" height=\"28\" alt=\"Request\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAAcCAQAAADYBBcfAAACvElEQVR42tVTbUhTYRTerDCnKVoUUr/KCZmypA9Koet0bXNLJ5XazDJ/WFaCUY0pExRZXxYiJgsxWWjkaL+yK+po1gjyR2QfmqWxtBmaBtqWGnabT++c11Fu4l/P4VzOPc95zoHznsNZodIbLDdRcKnc1Bu8DAK45ZsOnykQNMopsNooLxCknb0cDq5vml9FtHiIgpBR0R6iihYyFMTDt2Lg56ObPkI6TMGXSof1EV67IqCwisJSWliFAG/E0CfFIiebdNypcxi/1zgyFiIiZ3sJQr0RQx5frLa6k7SOKRo3oMFNR5t62h2rttKXEOKFqDCxtXNmmBokO2KKTlp3IdWuT2dYRNGKwEXEBCcL172G5FG0aIxC0kR9PBTVH1kkwQn+IqJnCE33EalVzT9GJQS1tAdD3CKicJYFrxqx7W2ejCEdZy1FiC5tZxHhLJKOZaRdQJAyV/YAvDliySALHxmxR4Hqe2iwvaOR/CEuZYJFSgYhVbZRkA8KGdEktrqnqra90NndCdkt77fjIHIhexOrfO6O3bbbOj/rqu5IptgyR3sU93QbOYhquZK4MCDp0Ina/PLsu5JvbCTRaapUdUmIV/RzoMdsk/0hWRNdAvKOmvqlN0drsJbJf1P4YsQ5lGrJeuosiOUgbOC8cto3LfOXTdVd7BqZsQKbse+0jUL6WPcesqs4MNSUTQAxGjwFiC8m3yzmqwHJBWYKBJ9WNqW/dHkpU/osch1Yj5RJfXPfSEe/2UPsN490NPfZG5CKyJmcV5ayHyzy7BMqsXfuHhGK/cjAIeSpR92gehR55D8TcQhDEKJwytBJ4fr4NULvrEM8NszfJPyxDoHYAQ1oPCWmIX4gifmDS/DV2DKeb25FHWr76yEG7/9L4YFPeiQQ4/8LkgJ8Et+NncTCsYqzXAEXa7CWdPZzGWdlyV+vST0JanfPvwAAAABJRU5ErkJggg==\" />
        <span class=\"sf-toolbar-status sf-toolbar-status-";
        // line 23
        echo twig_escape_filter($this->env, ($context["request_status_code_color"] ?? $this->getContext($context, "request_status_code_color")), "html", null, true);
        echo "\" title=\"";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "statustext", []), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "statuscode", []), "html", null, true);
        echo "</span>
        <span class=\"sf-toolbar-status sf-toolbar-info-piece-additional\">";
        // line 24
        echo twig_escape_filter($this->env, ($context["request_handler"] ?? $this->getContext($context, "request_handler")), "html", null, true);
        echo "</span>
        <span class=\"sf-toolbar-info-piece-additional-detail\">on <i>";
        // line 25
        echo twig_escape_filter($this->env, ($context["request_route"] ?? $this->getContext($context, "request_route")), "html", null, true);
        echo "</i></span>
    ";
        $context["icon"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 27
        echo "    ";
        ob_start(function () { return ''; });
        // line 28
        echo "        ";
        ob_start(function () { return ''; });
        // line 29
        echo "            <div class=\"sf-toolbar-info-piece\">
                <b>Status</b>
                <span class=\"sf-toolbar-status sf-toolbar-status-";
        // line 31
        echo twig_escape_filter($this->env, ($context["request_status_code_color"] ?? $this->getContext($context, "request_status_code_color")), "html", null, true);
        echo "\">";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "statuscode", []), "html", null, true);
        echo "</span> ";
        echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "statustext", []), "html", null, true);
        echo "
            </div>
            <div class=\"sf-toolbar-info-piece\">
                <b>Controller</b>
                ";
        // line 35
        echo twig_escape_filter($this->env, ($context["request_handler"] ?? $this->getContext($context, "request_handler")), "html", null, true);
        echo "
            </div>
            <div class=\"sf-toolbar-info-piece\">
                <b>Route name</b>
                <span>";
        // line 39
        echo twig_escape_filter($this->env, ($context["request_route"] ?? $this->getContext($context, "request_route")), "html", null, true);
        echo "</span>
            </div>
            <div class=\"sf-toolbar-info-piece\">
                <b>Has session</b>
                <span>";
        // line 43
        if (twig_length_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "sessionmetadata", []))) {
            echo "yes";
        } else {
            echo "no";
        }
        echo "</span>
            </div>
        ";
        echo trim(preg_replace('/>\s+</', '><', ob_get_clean()));
        // line 46
        echo "    ";
        $context["text"] = ('' === $tmp = ob_get_clean()) ? '' : new Markup($tmp, $this->env->getCharset());
        // line 47
        echo "    ";
        $this->loadTemplate("@WebProfiler/Profiler/toolbar_item.html.twig", "@WebProfiler/Collector/request.html.twig", 47)->display(twig_array_merge($context, ["link" => ($context["profiler_url"] ?? $this->getContext($context, "profiler_url"))]));
    }

    // line 50
    public function block_menu($context, array $blocks = [])
    {
        // line 51
        echo "<span class=\"label\">
    <span class=\"icon\"><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAcCAQAAACn1QXuAAAD2UlEQVR42p2Ve0zTVxTHKS4+KCBqNomCClgEJJAYkznQQIFaWltAiigsxGUgMy6b45HWV4UKUoP1yaMS0DqniVpngKlEMoMzW2Z0QTf4Ax/bdCzFCpQWq60U+Xp/baG/EoGf3vPH7/b3PffTc++55/w8xg+wji4W3ImDw4S3DgSD5fGhA+wcbRxclqsB+30RnmWcda1JPWn1poj8e3TYlvb/l6edTdSLWvYHgcUIdSwiuduxOOdu/n90WF7350648J+a0ClxYNWECglgahP+OyUOPpm34sDMNt6Ez+QwjniAKSzFgKWTw6L33x/3/yMHzU09l/XKlykj7krlXURNDlsEaVm/a8Fh48trUEEKGY4Zb5SaXUpZH4oROAlKvjijPu9GQfcY6jkOQoBlWIgARCAVVbtNo1rxky9/lqiV/hMmQfwXfRtZQxYVVoItC5aUpO8rDIcvYvUNqcN0n7TfJkyC+5lUdYIH9hlOkn3bCWbVCoJLLX9C9+FZEcoIpj2HYHh9XT92ZbUEFl7XSvfhD2EVI5imFh/DX948+lvWhgAEHL3kBrNhNSOYvImCdSgEb+wbGrmjomCFv46DrWn6hN+2QY6ZDYH8Tt6Dv+c4Yfn9bofbN8ABG/xHjYcMKmNHC0Tw/XOF0Ez3+VaH2BMZ1Ezclaynnm1x8LTDBo7U65Tm0tejrltPwwvzIcQO7EIKFsB3c8uoprAqzZruwQpE1cnpeMVxxZLNc8mFQQy2W9Tb+1xSplbjD18EEvM7sjTjuksp6rXVDBeVN29s5ztjFY1VSILpfJAHZiFkG1lAtyTD+gvZsix5emPSC3flm6v3JGvfxNvn+8zDt/HLFR3XUYI6RFPltERkYFro4j6Itdd5JB6JzaaGBAKUFtorpOsHRNoLveAxU1jRQ6xFQbaVNNFBpICN6YjZ00UpN0swj4KFPK/MtTJBffXKoETk3mouiYw7cmoLpsGzNVFkth+NpTKWgnkjof9MnjOflRYqsy4rfV1udebZatIgHhyB0XmylsyL2VXJjtQReMNWe9uGH5JN3ytMubY6HS7J9HSYTI/L1c9ybQoTQfEwG2HN52p7KixuEQ91PH5wEYkE5sRxUYJaFCCr4g+6o+o2slEMNVHjCYqF+RBjJ87m0OI/2YnvwMVCgnLi2AjCcgQgpGen1Mh1bATSgV4pghGISKKyqT6Gj+CHRUj/grT66sGOp7tIjOpmhGEGqYLxA174DOW4gjZiP6EMn2LWO7pz+O8N2nYcQhGq7v+ITZg3wYcPPghFDKibGUNm3u/qq5hL1PWIxgJEIRZBmE69fQsyes/JMSWb+gAAAABJRU5ErkJggg==\" alt=\"Request\"></span>
    <strong>Request</strong>
</span>
";
    }

    // line 57
    public function block_panel($context, array $blocks = [])
    {
        // line 58
        echo "    <h2>Request GET Parameters</h2>

    ";
        // line 60
        if (twig_length_filter($this->env, $this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestquery", []), "all", []))) {
            // line 61
            echo "        ";
            $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 61)->display(twig_to_array(["bag" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestquery", [])]));
            // line 62
            echo "    ";
        } else {
            // line 63
            echo "        <p>
            <em>No GET parameters</em>
        </p>
    ";
        }
        // line 67
        echo "
    <h2>Request POST Parameters</h2>

    ";
        // line 70
        if (twig_length_filter($this->env, $this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestrequest", []), "all", []))) {
            // line 71
            echo "        ";
            $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 71)->display(twig_to_array(["bag" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestrequest", [])]));
            // line 72
            echo "    ";
        } else {
            // line 73
            echo "        <p>
            <em>No POST parameters</em>
        </p>
    ";
        }
        // line 77
        echo "
    <h2>Request Attributes</h2>

    ";
        // line 80
        if (twig_length_filter($this->env, $this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestattributes", []), "all", []))) {
            // line 81
            echo "        ";
            $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 81)->display(twig_to_array(["bag" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestattributes", [])]));
            // line 82
            echo "    ";
        } else {
            // line 83
            echo "        <p>
            <em>No attributes</em>
        </p>
    ";
        }
        // line 87
        echo "
    <h2>Request Cookies</h2>

    ";
        // line 90
        if (twig_length_filter($this->env, $this->getAttribute($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestcookies", []), "all", []))) {
            // line 91
            echo "        ";
            $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 91)->display(twig_to_array(["bag" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestcookies", [])]));
            // line 92
            echo "    ";
        } else {
            // line 93
            echo "        <p>
            <em>No cookies</em>
        </p>
    ";
        }
        // line 97
        echo "
    <h2>Request Headers</h2>

    ";
        // line 100
        $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 100)->display(twig_to_array(["bag" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestheaders", [])]));
        // line 101
        echo "
    <h2>Request Content</h2>

    ";
        // line 104
        if (($this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "content", []) == false)) {
            // line 105
            echo "        <p><em>Request content not available (it was retrieved as a resource).</em></p>
    ";
        } elseif ($this->getAttribute(        // line 106
($context["collector"] ?? $this->getContext($context, "collector")), "content", [])) {
            // line 107
            echo "        <pre>";
            echo twig_escape_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "content", []), "html", null, true);
            echo "</pre>
    ";
        } else {
            // line 109
            echo "        <p><em>No content</em></p>
    ";
        }
        // line 111
        echo "
    <h2>Request Server Parameters</h2>

    ";
        // line 114
        $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 114)->display(twig_to_array(["bag" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "requestserver", [])]));
        // line 115
        echo "
    <h2>Response Headers</h2>

    ";
        // line 118
        $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 118)->display(twig_to_array(["bag" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "responseheaders", [])]));
        // line 119
        echo "
    <h2>Session Metadata</h2>

    ";
        // line 122
        if (twig_length_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "sessionmetadata", []))) {
            // line 123
            echo "    ";
            $this->loadTemplate("@WebProfiler/Profiler/table.html.twig", "@WebProfiler/Collector/request.html.twig", 123)->display(twig_to_array(["data" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "sessionmetadata", [])]));
            // line 124
            echo "    ";
        } else {
            // line 125
            echo "    <p>
        <em>No session metadata</em>
    </p>
    ";
        }
        // line 129
        echo "
    <h2>Session Attributes</h2>

    ";
        // line 132
        if (twig_length_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "sessionattributes", []))) {
            // line 133
            echo "        ";
            $this->loadTemplate("@WebProfiler/Profiler/table.html.twig", "@WebProfiler/Collector/request.html.twig", 133)->display(twig_to_array(["data" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "sessionattributes", [])]));
            // line 134
            echo "    ";
        } else {
            // line 135
            echo "        <p>
            <em>No session attributes</em>
        </p>
    ";
        }
        // line 139
        echo "
    <h2>Flashes</h2>

    ";
        // line 142
        if (twig_length_filter($this->env, $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "flashes", []))) {
            // line 143
            echo "        ";
            $this->loadTemplate("@WebProfiler/Profiler/table.html.twig", "@WebProfiler/Collector/request.html.twig", 143)->display(twig_to_array(["data" => $this->getAttribute(($context["collector"] ?? $this->getContext($context, "collector")), "flashes", [])]));
            // line 144
            echo "    ";
        } else {
            // line 145
            echo "        <p>
            <em>No flashes</em>
        </p>
    ";
        }
        // line 149
        echo "
    ";
        // line 150
        if ($this->getAttribute(($context["profile"] ?? $this->getContext($context, "profile")), "parent", [])) {
            // line 151
            echo "        <h2><a href=\"";
            echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_profiler", ["token" => $this->getAttribute($this->getAttribute(($context["profile"] ?? $this->getContext($context, "profile")), "parent", []), "token", [])]), "html", null, true);
            echo "\">Parent request: ";
            echo twig_escape_filter($this->env, $this->getAttribute($this->getAttribute(($context["profile"] ?? $this->getContext($context, "profile")), "parent", []), "token", []), "html", null, true);
            echo "</a></h2>

        ";
            // line 153
            $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 153)->display(twig_to_array(["bag" => $this->getAttribute($this->getAttribute($this->getAttribute(($context["profile"] ?? $this->getContext($context, "profile")), "parent", []), "getcollector", [0 => "request"], "method"), "requestattributes", [])]));
            // line 154
            echo "    ";
        }
        // line 155
        echo "
    ";
        // line 156
        if (twig_length_filter($this->env, $this->getAttribute(($context["profile"] ?? $this->getContext($context, "profile")), "children", []))) {
            // line 157
            echo "        <h2>Sub requests</h2>

        ";
            // line 159
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable($this->getAttribute(($context["profile"] ?? $this->getContext($context, "profile")), "children", []));
            foreach ($context['_seq'] as $context["_key"] => $context["child"]) {
                // line 160
                echo "            <h3><a href=\"";
                echo twig_escape_filter($this->env, $this->env->getExtension('Symfony\Bridge\Twig\Extension\RoutingExtension')->getPath("_profiler", ["token" => $this->getAttribute($context["child"], "token", [])]), "html", null, true);
                echo "\">";
                echo twig_escape_filter($this->env, $this->getAttribute($context["child"], "token", []), "html", null, true);
                echo "</a></h3>
            ";
                // line 161
                $this->loadTemplate("@WebProfiler/Profiler/bag.html.twig", "@WebProfiler/Collector/request.html.twig", 161)->display(twig_to_array(["bag" => $this->getAttribute($this->getAttribute($context["child"], "getcollector", [0 => "request"], "method"), "requestattributes", [])]));
                // line 162
                echo "        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['child'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 163
            echo "    ";
        }
        // line 164
        echo "
";
    }

    public function getTemplateName()
    {
        return "@WebProfiler/Collector/request.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  419 => 164,  416 => 163,  410 => 162,  408 => 161,  401 => 160,  397 => 159,  393 => 157,  391 => 156,  388 => 155,  385 => 154,  383 => 153,  375 => 151,  373 => 150,  370 => 149,  364 => 145,  361 => 144,  358 => 143,  356 => 142,  351 => 139,  345 => 135,  342 => 134,  339 => 133,  337 => 132,  332 => 129,  326 => 125,  323 => 124,  320 => 123,  318 => 122,  313 => 119,  311 => 118,  306 => 115,  304 => 114,  299 => 111,  295 => 109,  289 => 107,  287 => 106,  284 => 105,  282 => 104,  277 => 101,  275 => 100,  270 => 97,  264 => 93,  261 => 92,  258 => 91,  256 => 90,  251 => 87,  245 => 83,  242 => 82,  239 => 81,  237 => 80,  232 => 77,  226 => 73,  223 => 72,  220 => 71,  218 => 70,  213 => 67,  207 => 63,  204 => 62,  201 => 61,  199 => 60,  195 => 58,  192 => 57,  184 => 51,  181 => 50,  176 => 47,  173 => 46,  163 => 43,  156 => 39,  149 => 35,  138 => 31,  134 => 29,  131 => 28,  128 => 27,  123 => 25,  119 => 24,  111 => 23,  108 => 22,  105 => 21,  102 => 20,  99 => 19,  96 => 18,  90 => 16,  87 => 15,  75 => 13,  69 => 10,  61 => 9,  56 => 8,  53 => 7,  50 => 6,  47 => 5,  44 => 4,  41 => 3,  31 => 1,);
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
    {% set request_handler %}
        {% if collector.controller.class is defined %}
            {% set link = collector.controller.file|file_link(collector.controller.line) %}
            {% if collector.controller.method %}
                <span class=\"sf-toolbar-info-class sf-toolbar-info-with-next-pointer\">{{ collector.controller.class|abbr_class }}</span>
                <span class=\"sf-toolbar-info-method\"{% if link %} onclick=\"window.location='{{link|e('js')}}';window.event.stopPropagation();return false;\"{% endif %}>
                    {{ collector.controller.method }}
                </span>
            {% else %}
                <span class=\"sf-toolbar-info-class\"{% if link %} onclick=\"window.location='{{link|e('js')}}';window.event.stopPropagation();return false;\"{% endif %}>{{ collector.controller.class|abbr_class }}</span>
            {% endif %}
        {% else %}
            <span class=\"sf-toolbar-info-class\">{{ collector.controller }}</span>
        {% endif %}
    {% endset %}
    {% set request_status_code_color = (400 > collector.statuscode) ? ((200 == collector.statuscode) ? 'green' : 'yellow') : 'red'%}
    {% set request_route = collector.route ? collector.route : 'NONE' %}
    {% set icon %}
        <img width=\"28\" height=\"28\" alt=\"Request\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABwAAAAcCAQAAADYBBcfAAACvElEQVR42tVTbUhTYRTerDCnKVoUUr/KCZmypA9Koet0bXNLJ5XazDJ/WFaCUY0pExRZXxYiJgsxWWjkaL+yK+po1gjyR2QfmqWxtBmaBtqWGnabT++c11Fu4l/P4VzOPc95zoHznsNZodIbLDdRcKnc1Bu8DAK45ZsOnykQNMopsNooLxCknb0cDq5vml9FtHiIgpBR0R6iihYyFMTDt2Lg56ObPkI6TMGXSof1EV67IqCwisJSWliFAG/E0CfFIiebdNypcxi/1zgyFiIiZ3sJQr0RQx5frLa6k7SOKRo3oMFNR5t62h2rttKXEOKFqDCxtXNmmBokO2KKTlp3IdWuT2dYRNGKwEXEBCcL172G5FG0aIxC0kR9PBTVH1kkwQn+IqJnCE33EalVzT9GJQS1tAdD3CKicJYFrxqx7W2ejCEdZy1FiC5tZxHhLJKOZaRdQJAyV/YAvDliySALHxmxR4Hqe2iwvaOR/CEuZYJFSgYhVbZRkA8KGdEktrqnqra90NndCdkt77fjIHIhexOrfO6O3bbbOj/rqu5IptgyR3sU93QbOYhquZK4MCDp0Ina/PLsu5JvbCTRaapUdUmIV/RzoMdsk/0hWRNdAvKOmvqlN0drsJbJf1P4YsQ5lGrJeuosiOUgbOC8cto3LfOXTdVd7BqZsQKbse+0jUL6WPcesqs4MNSUTQAxGjwFiC8m3yzmqwHJBWYKBJ9WNqW/dHkpU/osch1Yj5RJfXPfSEe/2UPsN490NPfZG5CKyJmcV5ayHyzy7BMqsXfuHhGK/cjAIeSpR92gehR55D8TcQhDEKJwytBJ4fr4NULvrEM8NszfJPyxDoHYAQ1oPCWmIX4gifmDS/DV2DKeb25FHWr76yEG7/9L4YFPeiQQ4/8LkgJ8Et+NncTCsYqzXAEXa7CWdPZzGWdlyV+vST0JanfPvwAAAABJRU5ErkJggg==\" />
        <span class=\"sf-toolbar-status sf-toolbar-status-{{ request_status_code_color }}\" title=\"{{ collector.statustext }}\">{{ collector.statuscode }}</span>
        <span class=\"sf-toolbar-status sf-toolbar-info-piece-additional\">{{ request_handler }}</span>
        <span class=\"sf-toolbar-info-piece-additional-detail\">on <i>{{ request_route }}</i></span>
    {% endset %}
    {% set text %}
        {% spaceless %}
            <div class=\"sf-toolbar-info-piece\">
                <b>Status</b>
                <span class=\"sf-toolbar-status sf-toolbar-status-{{ request_status_code_color }}\">{{ collector.statuscode }}</span> {{ collector.statustext }}
            </div>
            <div class=\"sf-toolbar-info-piece\">
                <b>Controller</b>
                {{ request_handler }}
            </div>
            <div class=\"sf-toolbar-info-piece\">
                <b>Route name</b>
                <span>{{ request_route }}</span>
            </div>
            <div class=\"sf-toolbar-info-piece\">
                <b>Has session</b>
                <span>{% if collector.sessionmetadata|length %}yes{% else %}no{% endif %}</span>
            </div>
        {% endspaceless %}
    {% endset %}
    {% include '@WebProfiler/Profiler/toolbar_item.html.twig' with { 'link': profiler_url } %}
{% endblock %}

{% block menu %}
<span class=\"label\">
    <span class=\"icon\"><img src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACYAAAAcCAQAAACn1QXuAAAD2UlEQVR42p2Ve0zTVxTHKS4+KCBqNomCClgEJJAYkznQQIFaWltAiigsxGUgMy6b45HWV4UKUoP1yaMS0DqniVpngKlEMoMzW2Z0QTf4Ax/bdCzFCpQWq60U+Xp/baG/EoGf3vPH7/b3PffTc++55/w8xg+wji4W3ImDw4S3DgSD5fGhA+wcbRxclqsB+30RnmWcda1JPWn1poj8e3TYlvb/l6edTdSLWvYHgcUIdSwiuduxOOdu/n90WF7350648J+a0ClxYNWECglgahP+OyUOPpm34sDMNt6Ez+QwjniAKSzFgKWTw6L33x/3/yMHzU09l/XKlykj7krlXURNDlsEaVm/a8Fh48trUEEKGY4Zb5SaXUpZH4oROAlKvjijPu9GQfcY6jkOQoBlWIgARCAVVbtNo1rxky9/lqiV/hMmQfwXfRtZQxYVVoItC5aUpO8rDIcvYvUNqcN0n7TfJkyC+5lUdYIH9hlOkn3bCWbVCoJLLX9C9+FZEcoIpj2HYHh9XT92ZbUEFl7XSvfhD2EVI5imFh/DX948+lvWhgAEHL3kBrNhNSOYvImCdSgEb+wbGrmjomCFv46DrWn6hN+2QY6ZDYH8Tt6Dv+c4Yfn9bofbN8ABG/xHjYcMKmNHC0Tw/XOF0Ez3+VaH2BMZ1Ezclaynnm1x8LTDBo7U65Tm0tejrltPwwvzIcQO7EIKFsB3c8uoprAqzZruwQpE1cnpeMVxxZLNc8mFQQy2W9Tb+1xSplbjD18EEvM7sjTjuksp6rXVDBeVN29s5ztjFY1VSILpfJAHZiFkG1lAtyTD+gvZsix5emPSC3flm6v3JGvfxNvn+8zDt/HLFR3XUYI6RFPltERkYFro4j6Itdd5JB6JzaaGBAKUFtorpOsHRNoLveAxU1jRQ6xFQbaVNNFBpICN6YjZ00UpN0swj4KFPK/MtTJBffXKoETk3mouiYw7cmoLpsGzNVFkth+NpTKWgnkjof9MnjOflRYqsy4rfV1udebZatIgHhyB0XmylsyL2VXJjtQReMNWe9uGH5JN3ytMubY6HS7J9HSYTI/L1c9ybQoTQfEwG2HN52p7KixuEQ91PH5wEYkE5sRxUYJaFCCr4g+6o+o2slEMNVHjCYqF+RBjJ87m0OI/2YnvwMVCgnLi2AjCcgQgpGen1Mh1bATSgV4pghGISKKyqT6Gj+CHRUj/grT66sGOp7tIjOpmhGEGqYLxA174DOW4gjZiP6EMn2LWO7pz+O8N2nYcQhGq7v+ITZg3wYcPPghFDKibGUNm3u/qq5hL1PWIxgJEIRZBmE69fQsyes/JMSWb+gAAAABJRU5ErkJggg==\" alt=\"Request\"></span>
    <strong>Request</strong>
</span>
{% endblock %}

{% block panel %}
    <h2>Request GET Parameters</h2>

    {% if collector.requestquery.all|length %}
        {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': collector.requestquery } only %}
    {% else %}
        <p>
            <em>No GET parameters</em>
        </p>
    {% endif %}

    <h2>Request POST Parameters</h2>

    {% if collector.requestrequest.all|length %}
        {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': collector.requestrequest } only %}
    {% else %}
        <p>
            <em>No POST parameters</em>
        </p>
    {% endif %}

    <h2>Request Attributes</h2>

    {% if collector.requestattributes.all|length %}
        {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': collector.requestattributes } only %}
    {% else %}
        <p>
            <em>No attributes</em>
        </p>
    {% endif %}

    <h2>Request Cookies</h2>

    {% if collector.requestcookies.all|length %}
        {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': collector.requestcookies } only %}
    {% else %}
        <p>
            <em>No cookies</em>
        </p>
    {% endif %}

    <h2>Request Headers</h2>

    {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': collector.requestheaders } only %}

    <h2>Request Content</h2>

    {% if collector.content == false %}
        <p><em>Request content not available (it was retrieved as a resource).</em></p>
    {% elseif collector.content %}
        <pre>{{ collector.content }}</pre>
    {% else %}
        <p><em>No content</em></p>
    {% endif %}

    <h2>Request Server Parameters</h2>

    {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': collector.requestserver } only %}

    <h2>Response Headers</h2>

    {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': collector.responseheaders } only %}

    <h2>Session Metadata</h2>

    {% if collector.sessionmetadata|length %}
    {% include '@WebProfiler/Profiler/table.html.twig' with { 'data': collector.sessionmetadata } only %}
    {% else %}
    <p>
        <em>No session metadata</em>
    </p>
    {% endif %}

    <h2>Session Attributes</h2>

    {% if collector.sessionattributes|length %}
        {% include '@WebProfiler/Profiler/table.html.twig' with { 'data': collector.sessionattributes } only %}
    {% else %}
        <p>
            <em>No session attributes</em>
        </p>
    {% endif %}

    <h2>Flashes</h2>

    {% if collector.flashes|length %}
        {% include '@WebProfiler/Profiler/table.html.twig' with { 'data': collector.flashes } only %}
    {% else %}
        <p>
            <em>No flashes</em>
        </p>
    {% endif %}

    {% if profile.parent %}
        <h2><a href=\"{{ path('_profiler', { 'token': profile.parent.token }) }}\">Parent request: {{ profile.parent.token }}</a></h2>

        {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': profile.parent.getcollector('request').requestattributes } only %}
    {% endif %}

    {% if profile.children|length %}
        <h2>Sub requests</h2>

        {% for child in profile.children %}
            <h3><a href=\"{{ path('_profiler', { 'token': child.token }) }}\">{{ child.token }}</a></h3>
            {% include '@WebProfiler/Profiler/bag.html.twig' with { 'bag': child.getcollector('request').requestattributes } only %}
        {% endfor %}
    {% endif %}

{% endblock %}
", "@WebProfiler/Collector/request.html.twig", "/home/tuanminh/projects/journey/vendor/symfony/symfony/src/Symfony/Bundle/WebProfilerBundle/Resources/views/Collector/request.html.twig");
    }
}
