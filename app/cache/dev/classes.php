<?php 
namespace Symfony\Component\EventDispatcher
{
interface EventSubscriberInterface
{
public static function getSubscribedEvents();
}
}
namespace Symfony\Component\HttpKernel\EventListener
{
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
abstract class SessionListener implements EventSubscriberInterface
{
public function onKernelRequest(GetResponseEvent $event)
{
if (!$event->isMasterRequest()) {
return;
}
$request = $event->getRequest();
$session = $this->getSession();
if (null === $session || $request->hasSession()) {
return;
}
$request->setSession($session);
}
public static function getSubscribedEvents()
{
return array(
KernelEvents::REQUEST => array('onKernelRequest', 128),
);
}
abstract protected function getSession();
}
}
namespace Symfony\Bundle\FrameworkBundle\EventListener
{
use Symfony\Component\HttpKernel\EventListener\SessionListener as BaseSessionListener;
use Symfony\Component\DependencyInjection\ContainerInterface;
class SessionListener extends BaseSessionListener
{
private $container;
public function __construct(ContainerInterface $container)
{
$this->container = $container;
}
protected function getSession()
{
if (!$this->container->has('session')) {
return;
}
return $this->container->get('session');
}
}
}
namespace Symfony\Component\HttpFoundation\Session\Storage
{
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
interface SessionStorageInterface
{
public function start();
public function isStarted();
public function getId();
public function setId($id);
public function getName();
public function setName($name);
public function regenerate($destroy = false, $lifetime = null);
public function save();
public function clear();
public function getBag($name);
public function registerBag(SessionBagInterface $bag);
public function getMetadataBag();
}
}
namespace Symfony\Component\HttpFoundation\Session\Storage
{
use Symfony\Component\HttpFoundation\Session\SessionBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\NativeProxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\SessionHandlerProxy;
class NativeSessionStorage implements SessionStorageInterface
{
protected $bags;
protected $started = false;
protected $closed = false;
protected $saveHandler;
protected $metadataBag;
public function __construct(array $options = array(), $handler = null, MetadataBag $metaBag = null)
{
session_cache_limiter(''); ini_set('session.use_cookies', 1);
if (PHP_VERSION_ID >= 50400) {
session_register_shutdown();
} else {
register_shutdown_function('session_write_close');
}
$this->setMetadataBag($metaBag);
$this->setOptions($options);
$this->setSaveHandler($handler);
}
public function getSaveHandler()
{
return $this->saveHandler;
}
public function start()
{
if ($this->started) {
return true;
}
if (PHP_VERSION_ID >= 50400 && \PHP_SESSION_ACTIVE === session_status()) {
throw new \RuntimeException('Failed to start the session: already started by PHP.');
}
if (PHP_VERSION_ID < 50400 && !$this->closed && isset($_SESSION) && session_id()) {
throw new \RuntimeException('Failed to start the session: already started by PHP ($_SESSION is set).');
}
if (ini_get('session.use_cookies') && headers_sent($file, $line)) {
throw new \RuntimeException(sprintf('Failed to start the session because headers have already been sent by "%s" at line %d.', $file, $line));
}
if (!session_start()) {
throw new \RuntimeException('Failed to start the session');
}
$this->loadSession();
if (!$this->saveHandler->isWrapper() && !$this->saveHandler->isSessionHandlerInterface()) {
$this->saveHandler->setActive(true);
}
return true;
}
public function getId()
{
return $this->saveHandler->getId();
}
public function setId($id)
{
$this->saveHandler->setId($id);
}
public function getName()
{
return $this->saveHandler->getName();
}
public function setName($name)
{
$this->saveHandler->setName($name);
}
public function regenerate($destroy = false, $lifetime = null)
{
if (null !== $lifetime) {
ini_set('session.cookie_lifetime', $lifetime);
}
if ($destroy) {
$this->metadataBag->stampNew();
}
$isRegenerated = session_regenerate_id($destroy);
$this->loadSession();
return $isRegenerated;
}
public function save()
{
session_write_close();
if (!$this->saveHandler->isWrapper() && !$this->saveHandler->isSessionHandlerInterface()) {
$this->saveHandler->setActive(false);
}
$this->closed = true;
$this->started = false;
}
public function clear()
{
foreach ($this->bags as $bag) {
$bag->clear();
}
$_SESSION = array();
$this->loadSession();
}
public function registerBag(SessionBagInterface $bag)
{
$this->bags[$bag->getName()] = $bag;
}
public function getBag($name)
{
if (!isset($this->bags[$name])) {
throw new \InvalidArgumentException(sprintf('The SessionBagInterface %s is not registered.', $name));
}
if ($this->saveHandler->isActive() && !$this->started) {
$this->loadSession();
} elseif (!$this->started) {
$this->start();
}
return $this->bags[$name];
}
public function setMetadataBag(MetadataBag $metaBag = null)
{
if (null === $metaBag) {
$metaBag = new MetadataBag();
}
$this->metadataBag = $metaBag;
}
public function getMetadataBag()
{
return $this->metadataBag;
}
public function isStarted()
{
return $this->started;
}
public function setOptions(array $options)
{
$validOptions = array_flip(array('cache_limiter','cookie_domain','cookie_httponly','cookie_lifetime','cookie_path','cookie_secure','entropy_file','entropy_length','gc_divisor','gc_maxlifetime','gc_probability','hash_bits_per_character','hash_function','name','referer_check','serialize_handler','use_cookies','use_only_cookies','use_trans_sid','upload_progress.enabled','upload_progress.cleanup','upload_progress.prefix','upload_progress.name','upload_progress.freq','upload_progress.min-freq','url_rewriter.tags',
));
foreach ($options as $key => $value) {
if (isset($validOptions[$key])) {
ini_set('session.'.$key, $value);
}
}
}
public function setSaveHandler($saveHandler = null)
{
if (!$saveHandler instanceof AbstractProxy &&
!$saveHandler instanceof NativeSessionHandler &&
!$saveHandler instanceof \SessionHandlerInterface &&
null !== $saveHandler) {
throw new \InvalidArgumentException('Must be instance of AbstractProxy or NativeSessionHandler; implement \SessionHandlerInterface; or be null.');
}
if (!$saveHandler instanceof AbstractProxy && $saveHandler instanceof \SessionHandlerInterface) {
$saveHandler = new SessionHandlerProxy($saveHandler);
} elseif (!$saveHandler instanceof AbstractProxy) {
$saveHandler = PHP_VERSION_ID >= 50400 ?
new SessionHandlerProxy(new \SessionHandler()) : new NativeProxy();
}
$this->saveHandler = $saveHandler;
if ($this->saveHandler instanceof \SessionHandlerInterface) {
if (PHP_VERSION_ID >= 50400) {
session_set_save_handler($this->saveHandler, false);
} else {
session_set_save_handler(
array($this->saveHandler,'open'),
array($this->saveHandler,'close'),
array($this->saveHandler,'read'),
array($this->saveHandler,'write'),
array($this->saveHandler,'destroy'),
array($this->saveHandler,'gc')
);
}
}
}
protected function loadSession(array &$session = null)
{
if (null === $session) {
$session = &$_SESSION;
}
$bags = array_merge($this->bags, array($this->metadataBag));
foreach ($bags as $bag) {
$key = $bag->getStorageKey();
$session[$key] = isset($session[$key]) ? $session[$key] : array();
$bag->initialize($session[$key]);
}
$this->started = true;
$this->closed = false;
}
}
}
namespace Symfony\Component\HttpFoundation\Session\Storage
{
use Symfony\Component\HttpFoundation\Session\Storage\Proxy\AbstractProxy;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeSessionHandler;
class PhpBridgeSessionStorage extends NativeSessionStorage
{
public function __construct($handler = null, MetadataBag $metaBag = null)
{
$this->setMetadataBag($metaBag);
$this->setSaveHandler($handler);
}
public function start()
{
if ($this->started) {
return true;
}
$this->loadSession();
if (!$this->saveHandler->isWrapper() && !$this->saveHandler->isSessionHandlerInterface()) {
$this->saveHandler->setActive(true);
}
return true;
}
public function clear()
{
foreach ($this->bags as $bag) {
$bag->clear();
}
$this->loadSession();
}
}
}
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler
{
if (PHP_VERSION_ID >= 50400) {
class NativeSessionHandler extends \SessionHandler
{
}
} else {
class NativeSessionHandler
{
}
}
}
namespace Symfony\Component\HttpFoundation\Session\Storage\Handler
{
class NativeFileSessionHandler extends NativeSessionHandler
{
public function __construct($savePath = null)
{
if (null === $savePath) {
$savePath = ini_get('session.save_path');
}
$baseDir = $savePath;
if ($count = substr_count($savePath,';')) {
if ($count > 2) {
throw new \InvalidArgumentException(sprintf('Invalid argument $savePath \'%s\'', $savePath));
}
$baseDir = ltrim(strrchr($savePath,';'),';');
}
if ($baseDir && !is_dir($baseDir)) {
mkdir($baseDir, 0777, true);
}
ini_set('session.save_path', $savePath);
ini_set('session.save_handler','files');
}
}
}
namespace Symfony\Component\HttpFoundation\Session\Storage\Proxy
{
abstract class AbstractProxy
{
protected $wrapper = false;
protected $active = false;
protected $saveHandlerName;
public function getSaveHandlerName()
{
return $this->saveHandlerName;
}
public function isSessionHandlerInterface()
{
return ($this instanceof \SessionHandlerInterface);
}
public function isWrapper()
{
return $this->wrapper;
}
public function isActive()
{
if (PHP_VERSION_ID >= 50400) {
return $this->active = \PHP_SESSION_ACTIVE === session_status();
}
return $this->active;
}
public function setActive($flag)
{
if (PHP_VERSION_ID >= 50400) {
throw new \LogicException('This method is disabled in PHP 5.4.0+');
}
$this->active = (bool) $flag;
}
public function getId()
{
return session_id();
}
public function setId($id)
{
if ($this->isActive()) {
throw new \LogicException('Cannot change the ID of an active session');
}
session_id($id);
}
public function getName()
{
return session_name();
}
public function setName($name)
{
if ($this->isActive()) {
throw new \LogicException('Cannot change the name of an active session');
}
session_name($name);
}
}
}
namespace Symfony\Component\HttpFoundation\Session\Storage\Proxy
{
class SessionHandlerProxy extends AbstractProxy implements \SessionHandlerInterface
{
protected $handler;
public function __construct(\SessionHandlerInterface $handler)
{
$this->handler = $handler;
$this->wrapper = ($handler instanceof \SessionHandler);
$this->saveHandlerName = $this->wrapper ? ini_get('session.save_handler') :'user';
}
public function open($savePath, $sessionName)
{
$return = (bool) $this->handler->open($savePath, $sessionName);
if (true === $return) {
$this->active = true;
}
return $return;
}
public function close()
{
$this->active = false;
return (bool) $this->handler->close();
}
public function read($sessionId)
{
return (string) $this->handler->read($sessionId);
}
public function write($sessionId, $data)
{
return (bool) $this->handler->write($sessionId, $data);
}
public function destroy($sessionId)
{
return (bool) $this->handler->destroy($sessionId);
}
public function gc($maxlifetime)
{
return (bool) $this->handler->gc($maxlifetime);
}
}
}
namespace Symfony\Component\HttpFoundation\Session
{
use Symfony\Component\HttpFoundation\Session\Storage\MetadataBag;
interface SessionInterface
{
public function start();
public function getId();
public function setId($id);
public function getName();
public function setName($name);
public function invalidate($lifetime = null);
public function migrate($destroy = false, $lifetime = null);
public function save();
public function has($name);
public function get($name, $default = null);
public function set($name, $value);
public function all();
public function replace(array $attributes);
public function remove($name);
public function clear();
public function isStarted();
public function registerBag(SessionBagInterface $bag);
public function getBag($name);
public function getMetadataBag();
}
}
namespace Symfony\Component\HttpFoundation\Session
{
use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
class Session implements SessionInterface, \IteratorAggregate, \Countable
{
protected $storage;
private $flashName;
private $attributeName;
public function __construct(SessionStorageInterface $storage = null, AttributeBagInterface $attributes = null, FlashBagInterface $flashes = null)
{
$this->storage = $storage ?: new NativeSessionStorage();
$attributes = $attributes ?: new AttributeBag();
$this->attributeName = $attributes->getName();
$this->registerBag($attributes);
$flashes = $flashes ?: new FlashBag();
$this->flashName = $flashes->getName();
$this->registerBag($flashes);
}
public function start()
{
return $this->storage->start();
}
public function has($name)
{
return $this->storage->getBag($this->attributeName)->has($name);
}
public function get($name, $default = null)
{
return $this->storage->getBag($this->attributeName)->get($name, $default);
}
public function set($name, $value)
{
$this->storage->getBag($this->attributeName)->set($name, $value);
}
public function all()
{
return $this->storage->getBag($this->attributeName)->all();
}
public function replace(array $attributes)
{
$this->storage->getBag($this->attributeName)->replace($attributes);
}
public function remove($name)
{
return $this->storage->getBag($this->attributeName)->remove($name);
}
public function clear()
{
$this->storage->getBag($this->attributeName)->clear();
}
public function isStarted()
{
return $this->storage->isStarted();
}
public function getIterator()
{
return new \ArrayIterator($this->storage->getBag($this->attributeName)->all());
}
public function count()
{
return count($this->storage->getBag($this->attributeName)->all());
}
public function invalidate($lifetime = null)
{
$this->storage->clear();
return $this->migrate(true, $lifetime);
}
public function migrate($destroy = false, $lifetime = null)
{
return $this->storage->regenerate($destroy, $lifetime);
}
public function save()
{
$this->storage->save();
}
public function getId()
{
return $this->storage->getId();
}
public function setId($id)
{
$this->storage->setId($id);
}
public function getName()
{
return $this->storage->getName();
}
public function setName($name)
{
$this->storage->setName($name);
}
public function getMetadataBag()
{
return $this->storage->getMetadataBag();
}
public function registerBag(SessionBagInterface $bag)
{
$this->storage->registerBag($bag);
}
public function getBag($name)
{
return $this->storage->getBag($name);
}
public function getFlashBag()
{
return $this->getBag($this->flashName);
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Templating
{
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\SecurityContext;
class GlobalVariables
{
protected $container;
public function __construct(ContainerInterface $container)
{
$this->container = $container;
}
public function getSecurity()
{
if ($this->container->has('security.context')) {
return $this->container->get('security.context');
}
}
public function getUser()
{
if (!$this->container->has('security.token_storage')) {
return;
}
$tokenStorage = $this->container->get('security.token_storage');
if (!$token = $tokenStorage->getToken()) {
return;
}
$user = $token->getUser();
if (!is_object($user)) {
return;
}
return $user;
}
public function getRequest()
{
if ($this->container->has('request_stack')) {
return $this->container->get('request_stack')->getCurrentRequest();
}
}
public function getSession()
{
if ($request = $this->getRequest()) {
return $request->getSession();
}
}
public function getEnvironment()
{
return $this->container->getParameter('kernel.environment');
}
public function getDebug()
{
return (bool) $this->container->getParameter('kernel.debug');
}
}
}
namespace Symfony\Component\Templating
{
interface TemplateReferenceInterface
{
public function all();
public function set($name, $value);
public function get($name);
public function getPath();
public function getLogicalName();
public function __toString();
}
}
namespace Symfony\Component\Templating
{
class TemplateReference implements TemplateReferenceInterface
{
protected $parameters;
public function __construct($name = null, $engine = null)
{
$this->parameters = array('name'=> $name,'engine'=> $engine,
);
}
public function __toString()
{
return $this->getLogicalName();
}
public function set($name, $value)
{
if (array_key_exists($name, $this->parameters)) {
$this->parameters[$name] = $value;
} else {
throw new \InvalidArgumentException(sprintf('The template does not support the "%s" parameter.', $name));
}
return $this;
}
public function get($name)
{
if (array_key_exists($name, $this->parameters)) {
return $this->parameters[$name];
}
throw new \InvalidArgumentException(sprintf('The template does not support the "%s" parameter.', $name));
}
public function all()
{
return $this->parameters;
}
public function getPath()
{
return $this->parameters['name'];
}
public function getLogicalName()
{
return $this->parameters['name'];
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Templating
{
use Symfony\Component\Templating\TemplateReference as BaseTemplateReference;
class TemplateReference extends BaseTemplateReference
{
public function __construct($bundle = null, $controller = null, $name = null, $format = null, $engine = null)
{
$this->parameters = array('bundle'=> $bundle,'controller'=> $controller,'name'=> $name,'format'=> $format,'engine'=> $engine,
);
}
public function getPath()
{
$controller = str_replace('\\','/', $this->get('controller'));
$path = (empty($controller) ?'': $controller.'/').$this->get('name').'.'.$this->get('format').'.'.$this->get('engine');
return empty($this->parameters['bundle']) ?'views/'.$path :'@'.$this->get('bundle').'/Resources/views/'.$path;
}
public function getLogicalName()
{
return sprintf('%s:%s:%s.%s.%s', $this->parameters['bundle'], $this->parameters['controller'], $this->parameters['name'], $this->parameters['format'], $this->parameters['engine']);
}
}
}
namespace Symfony\Component\Templating
{
interface TemplateNameParserInterface
{
public function parse($name);
}
}
namespace Symfony\Component\Templating
{
class TemplateNameParser implements TemplateNameParserInterface
{
public function parse($name)
{
if ($name instanceof TemplateReferenceInterface) {
return $name;
}
$engine = null;
if (false !== $pos = strrpos($name,'.')) {
$engine = substr($name, $pos + 1);
}
return new TemplateReference($name, $engine);
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Templating
{
use Symfony\Component\Templating\TemplateReferenceInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Templating\TemplateNameParser as BaseTemplateNameParser;
class TemplateNameParser extends BaseTemplateNameParser
{
protected $kernel;
protected $cache = array();
public function __construct(KernelInterface $kernel)
{
$this->kernel = $kernel;
}
public function parse($name)
{
if ($name instanceof TemplateReferenceInterface) {
return $name;
} elseif (isset($this->cache[$name])) {
return $this->cache[$name];
}
$name = str_replace(':/',':', preg_replace('#/{2,}#','/', strtr($name,'\\','/')));
if (false !== strpos($name,'..')) {
throw new \RuntimeException(sprintf('Template name "%s" contains invalid characters.', $name));
}
if (!preg_match('/^([^:]*):([^:]*):(.+)\.([^\.]+)\.([^\.]+)$/', $name, $matches)) {
return parent::parse($name);
}
$template = new TemplateReference($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
if ($template->get('bundle')) {
try {
$this->kernel->getBundle($template->get('bundle'));
} catch (\Exception $e) {
throw new \InvalidArgumentException(sprintf('Template name "%s" is not valid.', $name), 0, $e);
}
}
return $this->cache[$name] = $template;
}
}
}
namespace Symfony\Component\Config
{
interface FileLocatorInterface
{
public function locate($name, $currentPath = null, $first = true);
}
}
namespace Symfony\Bundle\FrameworkBundle\Templating\Loader
{
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;
class TemplateLocator implements FileLocatorInterface
{
protected $locator;
protected $cache;
public function __construct(FileLocatorInterface $locator, $cacheDir = null)
{
if (null !== $cacheDir && is_file($cache = $cacheDir.'/templates.php')) {
$this->cache = require $cache;
}
$this->locator = $locator;
}
protected function getCacheKey($template)
{
return $template->getLogicalName();
}
public function locate($template, $currentPath = null, $first = true)
{
if (!$template instanceof TemplateReferenceInterface) {
throw new \InvalidArgumentException('The template must be an instance of TemplateReferenceInterface.');
}
$key = $this->getCacheKey($template);
if (isset($this->cache[$key])) {
return $this->cache[$key];
}
try {
return $this->cache[$key] = $this->locator->locate($template->getPath(), $currentPath);
} catch (\InvalidArgumentException $e) {
throw new \InvalidArgumentException(sprintf('Unable to find template "%s" : "%s".', $template, $e->getMessage()), 0, $e);
}
}
}
}
namespace Symfony\Component\Routing
{
interface RequestContextAwareInterface
{
public function setContext(RequestContext $context);
public function getContext();
}
}
namespace Symfony\Component\Routing\Generator
{
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RequestContextAwareInterface;
interface UrlGeneratorInterface extends RequestContextAwareInterface
{
const ABSOLUTE_URL = true;
const ABSOLUTE_PATH = false;
const RELATIVE_PATH ='relative';
const NETWORK_PATH ='network';
public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH);
}
}
namespace Symfony\Component\Routing\Generator
{
interface ConfigurableRequirementsInterface
{
public function setStrictRequirements($enabled);
public function isStrictRequirements();
}
}
namespace Symfony\Component\Routing\Generator
{
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Psr\Log\LoggerInterface;
class UrlGenerator implements UrlGeneratorInterface, ConfigurableRequirementsInterface
{
protected $routes;
protected $context;
protected $strictRequirements = true;
protected $logger;
protected $decodedChars = array('%2F'=>'/','%40'=>'@','%3A'=>':','%3B'=>';','%2C'=>',','%3D'=>'=','%2B'=>'+','%21'=>'!','%2A'=>'*','%7C'=>'|',
);
public function __construct(RouteCollection $routes, RequestContext $context, LoggerInterface $logger = null)
{
$this->routes = $routes;
$this->context = $context;
$this->logger = $logger;
}
public function setContext(RequestContext $context)
{
$this->context = $context;
}
public function getContext()
{
return $this->context;
}
public function setStrictRequirements($enabled)
{
$this->strictRequirements = null === $enabled ? null : (bool) $enabled;
}
public function isStrictRequirements()
{
return $this->strictRequirements;
}
public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
{
if (null === $route = $this->routes->get($name)) {
throw new RouteNotFoundException(sprintf('Unable to generate a URL for the named route "%s" as such route does not exist.', $name));
}
$compiledRoute = $route->compile();
return $this->doGenerate($compiledRoute->getVariables(), $route->getDefaults(), $route->getRequirements(), $compiledRoute->getTokens(), $parameters, $name, $referenceType, $compiledRoute->getHostTokens(), $route->getSchemes());
}
protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $referenceType, $hostTokens, array $requiredSchemes = array())
{
$variables = array_flip($variables);
$mergedParams = array_replace($defaults, $this->context->getParameters(), $parameters);
if ($diff = array_diff_key($variables, $mergedParams)) {
throw new MissingMandatoryParametersException(sprintf('Some mandatory parameters are missing ("%s") to generate a URL for route "%s".', implode('", "', array_keys($diff)), $name));
}
$url ='';
$optional = true;
foreach ($tokens as $token) {
if ('variable'=== $token[0]) {
if (!$optional || !array_key_exists($token[3], $defaults) || null !== $mergedParams[$token[3]] && (string) $mergedParams[$token[3]] !== (string) $defaults[$token[3]]) {
if (null !== $this->strictRequirements && !preg_match('#^'.$token[2].'$#', $mergedParams[$token[3]])) {
$message = sprintf('Parameter "%s" for route "%s" must match "%s" ("%s" given) to generate a corresponding URL.', $token[3], $name, $token[2], $mergedParams[$token[3]]);
if ($this->strictRequirements) {
throw new InvalidParameterException($message);
}
if ($this->logger) {
$this->logger->error($message);
}
return;
}
$url = $token[1].$mergedParams[$token[3]].$url;
$optional = false;
}
} else {
$url = $token[1].$url;
$optional = false;
}
}
if (''=== $url) {
$url ='/';
}
$url = strtr(rawurlencode($url), $this->decodedChars);
$url = strtr($url, array('/../'=>'/%2E%2E/','/./'=>'/%2E/'));
if ('/..'=== substr($url, -3)) {
$url = substr($url, 0, -2).'%2E%2E';
} elseif ('/.'=== substr($url, -2)) {
$url = substr($url, 0, -1).'%2E';
}
$schemeAuthority ='';
if ($host = $this->context->getHost()) {
$scheme = $this->context->getScheme();
if ($requiredSchemes) {
$schemeMatched = false;
foreach ($requiredSchemes as $requiredScheme) {
if ($scheme === $requiredScheme) {
$schemeMatched = true;
break;
}
}
if (!$schemeMatched) {
$referenceType = self::ABSOLUTE_URL;
$scheme = current($requiredSchemes);
}
} elseif (isset($requirements['_scheme']) && ($req = strtolower($requirements['_scheme'])) && $scheme !== $req) {
$referenceType = self::ABSOLUTE_URL;
$scheme = $req;
}
if ($hostTokens) {
$routeHost ='';
foreach ($hostTokens as $token) {
if ('variable'=== $token[0]) {
if (null !== $this->strictRequirements && !preg_match('#^'.$token[2].'$#i', $mergedParams[$token[3]])) {
$message = sprintf('Parameter "%s" for route "%s" must match "%s" ("%s" given) to generate a corresponding URL.', $token[3], $name, $token[2], $mergedParams[$token[3]]);
if ($this->strictRequirements) {
throw new InvalidParameterException($message);
}
if ($this->logger) {
$this->logger->error($message);
}
return;
}
$routeHost = $token[1].$mergedParams[$token[3]].$routeHost;
} else {
$routeHost = $token[1].$routeHost;
}
}
if ($routeHost !== $host) {
$host = $routeHost;
if (self::ABSOLUTE_URL !== $referenceType) {
$referenceType = self::NETWORK_PATH;
}
}
}
if (self::ABSOLUTE_URL === $referenceType || self::NETWORK_PATH === $referenceType) {
$port ='';
if ('http'=== $scheme && 80 != $this->context->getHttpPort()) {
$port =':'.$this->context->getHttpPort();
} elseif ('https'=== $scheme && 443 != $this->context->getHttpsPort()) {
$port =':'.$this->context->getHttpsPort();
}
$schemeAuthority = self::NETWORK_PATH === $referenceType ?'//': "$scheme://";
$schemeAuthority .= $host.$port;
}
}
if (self::RELATIVE_PATH === $referenceType) {
$url = self::getRelativePath($this->context->getPathInfo(), $url);
} else {
$url = $schemeAuthority.$this->context->getBaseUrl().$url;
}
$extra = array_diff_key($parameters, $variables, $defaults);
if ($extra && $query = http_build_query($extra,'','&')) {
$url .='?'.strtr($query, array('%2F'=>'/'));
}
return $url;
}
public static function getRelativePath($basePath, $targetPath)
{
if ($basePath === $targetPath) {
return'';
}
$sourceDirs = explode('/', isset($basePath[0]) &&'/'=== $basePath[0] ? substr($basePath, 1) : $basePath);
$targetDirs = explode('/', isset($targetPath[0]) &&'/'=== $targetPath[0] ? substr($targetPath, 1) : $targetPath);
array_pop($sourceDirs);
$targetFile = array_pop($targetDirs);
foreach ($sourceDirs as $i => $dir) {
if (isset($targetDirs[$i]) && $dir === $targetDirs[$i]) {
unset($sourceDirs[$i], $targetDirs[$i]);
} else {
break;
}
}
$targetDirs[] = $targetFile;
$path = str_repeat('../', count($sourceDirs)).implode('/', $targetDirs);
return''=== $path ||'/'=== $path[0]
|| false !== ($colonPos = strpos($path,':')) && ($colonPos < ($slashPos = strpos($path,'/')) || false === $slashPos)
? "./$path" : $path;
}
}
}
namespace Symfony\Component\Routing
{
use Symfony\Component\HttpFoundation\Request;
class RequestContext
{
private $baseUrl;
private $pathInfo;
private $method;
private $host;
private $scheme;
private $httpPort;
private $httpsPort;
private $queryString;
private $parameters = array();
public function __construct($baseUrl ='', $method ='GET', $host ='localhost', $scheme ='http', $httpPort = 80, $httpsPort = 443, $path ='/', $queryString ='')
{
$this->setBaseUrl($baseUrl);
$this->setMethod($method);
$this->setHost($host);
$this->setScheme($scheme);
$this->setHttpPort($httpPort);
$this->setHttpsPort($httpsPort);
$this->setPathInfo($path);
$this->setQueryString($queryString);
}
public function fromRequest(Request $request)
{
$this->setBaseUrl($request->getBaseUrl());
$this->setPathInfo($request->getPathInfo());
$this->setMethod($request->getMethod());
$this->setHost($request->getHost());
$this->setScheme($request->getScheme());
$this->setHttpPort($request->isSecure() ? $this->httpPort : $request->getPort());
$this->setHttpsPort($request->isSecure() ? $request->getPort() : $this->httpsPort);
$this->setQueryString($request->server->get('QUERY_STRING',''));
return $this;
}
public function getBaseUrl()
{
return $this->baseUrl;
}
public function setBaseUrl($baseUrl)
{
$this->baseUrl = $baseUrl;
return $this;
}
public function getPathInfo()
{
return $this->pathInfo;
}
public function setPathInfo($pathInfo)
{
$this->pathInfo = $pathInfo;
return $this;
}
public function getMethod()
{
return $this->method;
}
public function setMethod($method)
{
$this->method = strtoupper($method);
return $this;
}
public function getHost()
{
return $this->host;
}
public function setHost($host)
{
$this->host = strtolower($host);
return $this;
}
public function getScheme()
{
return $this->scheme;
}
public function setScheme($scheme)
{
$this->scheme = strtolower($scheme);
return $this;
}
public function getHttpPort()
{
return $this->httpPort;
}
public function setHttpPort($httpPort)
{
$this->httpPort = (int) $httpPort;
return $this;
}
public function getHttpsPort()
{
return $this->httpsPort;
}
public function setHttpsPort($httpsPort)
{
$this->httpsPort = (int) $httpsPort;
return $this;
}
public function getQueryString()
{
return $this->queryString;
}
public function setQueryString($queryString)
{
$this->queryString = (string) $queryString;
return $this;
}
public function getParameters()
{
return $this->parameters;
}
public function setParameters(array $parameters)
{
$this->parameters = $parameters;
return $this;
}
public function getParameter($name)
{
return isset($this->parameters[$name]) ? $this->parameters[$name] : null;
}
public function hasParameter($name)
{
return array_key_exists($name, $this->parameters);
}
public function setParameter($name, $parameter)
{
$this->parameters[$name] = $parameter;
return $this;
}
}
}
namespace Symfony\Component\Routing\Matcher
{
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
interface UrlMatcherInterface extends RequestContextAwareInterface
{
public function match($pathinfo);
}
}
namespace Symfony\Component\Routing
{
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
interface RouterInterface extends UrlMatcherInterface, UrlGeneratorInterface
{
public function getRouteCollection();
}
}
namespace Symfony\Component\Routing\Matcher
{
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
interface RequestMatcherInterface
{
public function matchRequest(Request $request);
}
}
namespace Symfony\Component\Routing
{
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\ConfigCache;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\ConfigurableRequirementsInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Generator\Dumper\GeneratorDumperInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Matcher\Dumper\MatcherDumperInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
class Router implements RouterInterface, RequestMatcherInterface
{
protected $matcher;
protected $generator;
protected $context;
protected $loader;
protected $collection;
protected $resource;
protected $options = array();
protected $logger;
private $expressionLanguageProviders = array();
public function __construct(LoaderInterface $loader, $resource, array $options = array(), RequestContext $context = null, LoggerInterface $logger = null)
{
$this->loader = $loader;
$this->resource = $resource;
$this->logger = $logger;
$this->context = $context ?: new RequestContext();
$this->setOptions($options);
}
public function setOptions(array $options)
{
$this->options = array('cache_dir'=> null,'debug'=> false,'generator_class'=>'Symfony\\Component\\Routing\\Generator\\UrlGenerator','generator_base_class'=>'Symfony\\Component\\Routing\\Generator\\UrlGenerator','generator_dumper_class'=>'Symfony\\Component\\Routing\\Generator\\Dumper\\PhpGeneratorDumper','generator_cache_class'=>'ProjectUrlGenerator','matcher_class'=>'Symfony\\Component\\Routing\\Matcher\\UrlMatcher','matcher_base_class'=>'Symfony\\Component\\Routing\\Matcher\\UrlMatcher','matcher_dumper_class'=>'Symfony\\Component\\Routing\\Matcher\\Dumper\\PhpMatcherDumper','matcher_cache_class'=>'ProjectUrlMatcher','resource_type'=> null,'strict_requirements'=> true,
);
$invalid = array();
foreach ($options as $key => $value) {
if (array_key_exists($key, $this->options)) {
$this->options[$key] = $value;
} else {
$invalid[] = $key;
}
}
if ($invalid) {
throw new \InvalidArgumentException(sprintf('The Router does not support the following options: "%s".', implode('", "', $invalid)));
}
}
public function setOption($key, $value)
{
if (!array_key_exists($key, $this->options)) {
throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
}
$this->options[$key] = $value;
}
public function getOption($key)
{
if (!array_key_exists($key, $this->options)) {
throw new \InvalidArgumentException(sprintf('The Router does not support the "%s" option.', $key));
}
return $this->options[$key];
}
public function getRouteCollection()
{
if (null === $this->collection) {
$this->collection = $this->loader->load($this->resource, $this->options['resource_type']);
}
return $this->collection;
}
public function setContext(RequestContext $context)
{
$this->context = $context;
if (null !== $this->matcher) {
$this->getMatcher()->setContext($context);
}
if (null !== $this->generator) {
$this->getGenerator()->setContext($context);
}
}
public function getContext()
{
return $this->context;
}
public function generate($name, $parameters = array(), $referenceType = self::ABSOLUTE_PATH)
{
return $this->getGenerator()->generate($name, $parameters, $referenceType);
}
public function match($pathinfo)
{
return $this->getMatcher()->match($pathinfo);
}
public function matchRequest(Request $request)
{
$matcher = $this->getMatcher();
if (!$matcher instanceof RequestMatcherInterface) {
return $matcher->match($request->getPathInfo());
}
return $matcher->matchRequest($request);
}
public function getMatcher()
{
if (null !== $this->matcher) {
return $this->matcher;
}
if (null === $this->options['cache_dir'] || null === $this->options['matcher_cache_class']) {
$this->matcher = new $this->options['matcher_class']($this->getRouteCollection(), $this->context);
if (method_exists($this->matcher,'addExpressionLanguageProvider')) {
foreach ($this->expressionLanguageProviders as $provider) {
$this->matcher->addExpressionLanguageProvider($provider);
}
}
return $this->matcher;
}
$class = $this->options['matcher_cache_class'];
$cache = new ConfigCache($this->options['cache_dir'].'/'.$class.'.php', $this->options['debug']);
if (!$cache->isFresh()) {
$dumper = $this->getMatcherDumperInstance();
if (method_exists($dumper,'addExpressionLanguageProvider')) {
foreach ($this->expressionLanguageProviders as $provider) {
$dumper->addExpressionLanguageProvider($provider);
}
}
$options = array('class'=> $class,'base_class'=> $this->options['matcher_base_class'],
);
$cache->write($dumper->dump($options), $this->getRouteCollection()->getResources());
}
require_once $cache;
return $this->matcher = new $class($this->context);
}
public function getGenerator()
{
if (null !== $this->generator) {
return $this->generator;
}
if (null === $this->options['cache_dir'] || null === $this->options['generator_cache_class']) {
$this->generator = new $this->options['generator_class']($this->getRouteCollection(), $this->context, $this->logger);
} else {
$class = $this->options['generator_cache_class'];
$cache = new ConfigCache($this->options['cache_dir'].'/'.$class.'.php', $this->options['debug']);
if (!$cache->isFresh()) {
$dumper = $this->getGeneratorDumperInstance();
$options = array('class'=> $class,'base_class'=> $this->options['generator_base_class'],
);
$cache->write($dumper->dump($options), $this->getRouteCollection()->getResources());
}
require_once $cache;
$this->generator = new $class($this->context, $this->logger);
}
if ($this->generator instanceof ConfigurableRequirementsInterface) {
$this->generator->setStrictRequirements($this->options['strict_requirements']);
}
return $this->generator;
}
public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider)
{
$this->expressionLanguageProviders[] = $provider;
}
protected function getGeneratorDumperInstance()
{
return new $this->options['generator_dumper_class']($this->getRouteCollection());
}
protected function getMatcherDumperInstance()
{
return new $this->options['matcher_dumper_class']($this->getRouteCollection());
}
}
}
namespace Symfony\Component\Routing\Matcher
{
interface RedirectableUrlMatcherInterface
{
public function redirect($path, $route, $scheme = null);
}
}
namespace Symfony\Component\Routing\Matcher
{
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
class UrlMatcher implements UrlMatcherInterface, RequestMatcherInterface
{
const REQUIREMENT_MATCH = 0;
const REQUIREMENT_MISMATCH = 1;
const ROUTE_MATCH = 2;
protected $context;
protected $allow = array();
protected $routes;
protected $request;
protected $expressionLanguage;
protected $expressionLanguageProviders = array();
public function __construct(RouteCollection $routes, RequestContext $context)
{
$this->routes = $routes;
$this->context = $context;
}
public function setContext(RequestContext $context)
{
$this->context = $context;
}
public function getContext()
{
return $this->context;
}
public function match($pathinfo)
{
$this->allow = array();
if ($ret = $this->matchCollection(rawurldecode($pathinfo), $this->routes)) {
return $ret;
}
throw 0 < count($this->allow)
? new MethodNotAllowedException(array_unique(array_map('strtoupper', $this->allow)))
: new ResourceNotFoundException(sprintf('No routes found for "%s".', $pathinfo));
}
public function matchRequest(Request $request)
{
$this->request = $request;
$ret = $this->match($request->getPathInfo());
$this->request = null;
return $ret;
}
public function addExpressionLanguageProvider(ExpressionFunctionProviderInterface $provider)
{
$this->expressionLanguageProviders[] = $provider;
}
protected function matchCollection($pathinfo, RouteCollection $routes)
{
foreach ($routes as $name => $route) {
$compiledRoute = $route->compile();
if (''!== $compiledRoute->getStaticPrefix() && 0 !== strpos($pathinfo, $compiledRoute->getStaticPrefix())) {
continue;
}
if (!preg_match($compiledRoute->getRegex(), $pathinfo, $matches)) {
continue;
}
$hostMatches = array();
if ($compiledRoute->getHostRegex() && !preg_match($compiledRoute->getHostRegex(), $this->context->getHost(), $hostMatches)) {
continue;
}
if ($req = $route->getRequirement('_method')) {
if ('HEAD'=== $method = $this->context->getMethod()) {
$method ='GET';
}
if (!in_array($method, $req = explode('|', strtoupper($req)))) {
$this->allow = array_merge($this->allow, $req);
continue;
}
}
$status = $this->handleRouteRequirements($pathinfo, $name, $route);
if (self::ROUTE_MATCH === $status[0]) {
return $status[1];
}
if (self::REQUIREMENT_MISMATCH === $status[0]) {
continue;
}
return $this->getAttributes($route, $name, array_replace($matches, $hostMatches));
}
}
protected function getAttributes(Route $route, $name, array $attributes)
{
$attributes['_route'] = $name;
return $this->mergeDefaults($attributes, $route->getDefaults());
}
protected function handleRouteRequirements($pathinfo, $name, Route $route)
{
if ($route->getCondition() && !$this->getExpressionLanguage()->evaluate($route->getCondition(), array('context'=> $this->context,'request'=> $this->request))) {
return array(self::REQUIREMENT_MISMATCH, null);
}
$scheme = $this->context->getScheme();
$status = $route->getSchemes() && !$route->hasScheme($scheme) ? self::REQUIREMENT_MISMATCH : self::REQUIREMENT_MATCH;
return array($status, null);
}
protected function mergeDefaults($params, $defaults)
{
foreach ($params as $key => $value) {
if (!is_int($key)) {
$defaults[$key] = $value;
}
}
return $defaults;
}
protected function getExpressionLanguage()
{
if (null === $this->expressionLanguage) {
if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
throw new \RuntimeException('Unable to use expressions as the Symfony ExpressionLanguage component is not installed.');
}
$this->expressionLanguage = new ExpressionLanguage(null, $this->expressionLanguageProviders);
}
return $this->expressionLanguage;
}
}
}
namespace Symfony\Component\Routing\Matcher
{
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Route;
abstract class RedirectableUrlMatcher extends UrlMatcher implements RedirectableUrlMatcherInterface
{
public function match($pathinfo)
{
try {
$parameters = parent::match($pathinfo);
} catch (ResourceNotFoundException $e) {
if ('/'=== substr($pathinfo, -1) || !in_array($this->context->getMethod(), array('HEAD','GET'))) {
throw $e;
}
try {
parent::match($pathinfo.'/');
return $this->redirect($pathinfo.'/', null);
} catch (ResourceNotFoundException $e2) {
throw $e;
}
}
return $parameters;
}
protected function handleRouteRequirements($pathinfo, $name, Route $route)
{
if ($route->getCondition() && !$this->getExpressionLanguage()->evaluate($route->getCondition(), array('context'=> $this->context,'request'=> $this->request))) {
return array(self::REQUIREMENT_MISMATCH, null);
}
$scheme = $this->context->getScheme();
$schemes = $route->getSchemes();
if ($schemes && !$route->hasScheme($scheme)) {
return array(self::ROUTE_MATCH, $this->redirect($pathinfo, $name, current($schemes)));
}
return array(self::REQUIREMENT_MATCH, null);
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Routing
{
use Symfony\Component\Routing\Matcher\RedirectableUrlMatcher as BaseMatcher;
class RedirectableUrlMatcher extends BaseMatcher
{
public function redirect($path, $route, $scheme = null)
{
return array('_controller'=>'Symfony\\Bundle\\FrameworkBundle\\Controller\\RedirectController::urlRedirectAction','path'=> $path,'permanent'=> true,'scheme'=> $scheme,'httpPort'=> $this->context->getHttpPort(),'httpsPort'=> $this->context->getHttpsPort(),'_route'=> $route,
);
}
}
}
namespace Symfony\Component\HttpKernel\CacheWarmer
{
interface WarmableInterface
{
public function warmUp($cacheDir);
}
}
namespace Symfony\Bundle\FrameworkBundle\Routing
{
use Symfony\Component\Routing\Router as BaseRouter;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\HttpKernel\CacheWarmer\WarmableInterface;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
class Router extends BaseRouter implements WarmableInterface
{
private $container;
public function __construct(ContainerInterface $container, $resource, array $options = array(), RequestContext $context = null)
{
$this->container = $container;
$this->resource = $resource;
$this->context = $context ?: new RequestContext();
$this->setOptions($options);
}
public function getRouteCollection()
{
if (null === $this->collection) {
$this->collection = $this->container->get('routing.loader')->load($this->resource, $this->options['resource_type']);
$this->resolveParameters($this->collection);
}
return $this->collection;
}
public function warmUp($cacheDir)
{
$currentDir = $this->getOption('cache_dir');
$this->setOption('cache_dir', $cacheDir);
$this->getMatcher();
$this->getGenerator();
$this->setOption('cache_dir', $currentDir);
}
private function resolveParameters(RouteCollection $collection)
{
foreach ($collection as $route) {
foreach ($route->getDefaults() as $name => $value) {
$route->setDefault($name, $this->resolve($value));
}
foreach ($route->getRequirements() as $name => $value) {
$route->setRequirement($name, $this->resolve($value));
}
$route->setPath($this->resolve($route->getPath()));
$route->setHost($this->resolve($route->getHost()));
}
}
private function resolve($value)
{
if (is_array($value)) {
foreach ($value as $key => $val) {
$value[$key] = $this->resolve($val);
}
return $value;
}
if (!is_string($value)) {
return $value;
}
$container = $this->container;
$escapedValue = preg_replace_callback('/%%|%([^%\s]++)%/', function ($match) use ($container, $value) {
if (!isset($match[1])) {
return'%%';
}
$resolved = $container->getParameter($match[1]);
if (is_string($resolved) || is_numeric($resolved)) {
return (string) $resolved;
}
throw new RuntimeException(sprintf('The container parameter "%s", used in the route configuration value "%s", '.'must be a string or numeric, but it is of type %s.',
$match[1],
$value,
gettype($resolved)
)
);
}, $value);
return str_replace('%%','%', $escapedValue);
}
}
}
namespace Symfony\Component\Config
{
class FileLocator implements FileLocatorInterface
{
protected $paths;
public function __construct($paths = array())
{
$this->paths = (array) $paths;
}
public function locate($name, $currentPath = null, $first = true)
{
if (''== $name) {
throw new \InvalidArgumentException('An empty file name is not valid to be located.');
}
if ($this->isAbsolutePath($name)) {
if (!file_exists($name)) {
throw new \InvalidArgumentException(sprintf('The file "%s" does not exist.', $name));
}
return $name;
}
$paths = $this->paths;
if (null !== $currentPath) {
array_unshift($paths, $currentPath);
}
$paths = array_unique($paths);
$filepaths = array();
foreach ($paths as $path) {
if (file_exists($file = $path.DIRECTORY_SEPARATOR.$name)) {
if (true === $first) {
return $file;
}
$filepaths[] = $file;
}
}
if (!$filepaths) {
throw new \InvalidArgumentException(sprintf('The file "%s" does not exist (in: %s).', $name, implode(', ', $paths)));
}
return $filepaths;
}
private function isAbsolutePath($file)
{
if ($file[0] ==='/'|| $file[0] ==='\\'|| (strlen($file) > 3 && ctype_alpha($file[0])
&& $file[1] ===':'&& ($file[2] ==='\\'|| $file[2] ==='/')
)
|| null !== parse_url($file, PHP_URL_SCHEME)
) {
return true;
}
return false;
}
}
}
namespace Symfony\Component\EventDispatcher
{
class Event
{
private $propagationStopped = false;
private $dispatcher;
private $name;
public function isPropagationStopped()
{
return $this->propagationStopped;
}
public function stopPropagation()
{
$this->propagationStopped = true;
}
public function setDispatcher(EventDispatcherInterface $dispatcher)
{
$this->dispatcher = $dispatcher;
}
public function getDispatcher()
{
return $this->dispatcher;
}
public function getName()
{
return $this->name;
}
public function setName($name)
{
$this->name = $name;
}
}
}
namespace Symfony\Component\EventDispatcher
{
interface EventDispatcherInterface
{
public function dispatch($eventName, Event $event = null);
public function addListener($eventName, $listener, $priority = 0);
public function addSubscriber(EventSubscriberInterface $subscriber);
public function removeListener($eventName, $listener);
public function removeSubscriber(EventSubscriberInterface $subscriber);
public function getListeners($eventName = null);
public function hasListeners($eventName = null);
}
}
namespace Symfony\Component\EventDispatcher
{
class EventDispatcher implements EventDispatcherInterface
{
private $listeners = array();
private $sorted = array();
public function dispatch($eventName, Event $event = null)
{
if (null === $event) {
$event = new Event();
}
$event->setDispatcher($this);
$event->setName($eventName);
if (!isset($this->listeners[$eventName])) {
return $event;
}
$this->doDispatch($this->getListeners($eventName), $eventName, $event);
return $event;
}
public function getListeners($eventName = null)
{
if (null !== $eventName) {
if (!isset($this->sorted[$eventName])) {
$this->sortListeners($eventName);
}
return $this->sorted[$eventName];
}
foreach ($this->listeners as $eventName => $eventListeners) {
if (!isset($this->sorted[$eventName])) {
$this->sortListeners($eventName);
}
}
return array_filter($this->sorted);
}
public function hasListeners($eventName = null)
{
return (bool) count($this->getListeners($eventName));
}
public function addListener($eventName, $listener, $priority = 0)
{
$this->listeners[$eventName][$priority][] = $listener;
unset($this->sorted[$eventName]);
}
public function removeListener($eventName, $listener)
{
if (!isset($this->listeners[$eventName])) {
return;
}
foreach ($this->listeners[$eventName] as $priority => $listeners) {
if (false !== ($key = array_search($listener, $listeners, true))) {
unset($this->listeners[$eventName][$priority][$key], $this->sorted[$eventName]);
}
}
}
public function addSubscriber(EventSubscriberInterface $subscriber)
{
foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
if (is_string($params)) {
$this->addListener($eventName, array($subscriber, $params));
} elseif (is_string($params[0])) {
$this->addListener($eventName, array($subscriber, $params[0]), isset($params[1]) ? $params[1] : 0);
} else {
foreach ($params as $listener) {
$this->addListener($eventName, array($subscriber, $listener[0]), isset($listener[1]) ? $listener[1] : 0);
}
}
}
}
public function removeSubscriber(EventSubscriberInterface $subscriber)
{
foreach ($subscriber->getSubscribedEvents() as $eventName => $params) {
if (is_array($params) && is_array($params[0])) {
foreach ($params as $listener) {
$this->removeListener($eventName, array($subscriber, $listener[0]));
}
} else {
$this->removeListener($eventName, array($subscriber, is_string($params) ? $params : $params[0]));
}
}
}
protected function doDispatch($listeners, $eventName, Event $event)
{
foreach ($listeners as $listener) {
call_user_func($listener, $event, $eventName, $this);
if ($event->isPropagationStopped()) {
break;
}
}
}
private function sortListeners($eventName)
{
$this->sorted[$eventName] = array();
if (isset($this->listeners[$eventName])) {
krsort($this->listeners[$eventName]);
$this->sorted[$eventName] = call_user_func_array('array_merge', $this->listeners[$eventName]);
}
}
}
}
namespace Symfony\Component\EventDispatcher
{
use Symfony\Component\DependencyInjection\ContainerInterface;
class ContainerAwareEventDispatcher extends EventDispatcher
{
private $container;
private $listenerIds = array();
private $listeners = array();
public function __construct(ContainerInterface $container)
{
$this->container = $container;
}
public function addListenerService($eventName, $callback, $priority = 0)
{
if (!is_array($callback) || 2 !== count($callback)) {
throw new \InvalidArgumentException('Expected an array("service", "method") argument');
}
$this->listenerIds[$eventName][] = array($callback[0], $callback[1], $priority);
}
public function removeListener($eventName, $listener)
{
$this->lazyLoad($eventName);
if (isset($this->listenerIds[$eventName])) {
foreach ($this->listenerIds[$eventName] as $i => $args) {
list($serviceId, $method, $priority) = $args;
$key = $serviceId.'.'.$method;
if (isset($this->listeners[$eventName][$key]) && $listener === array($this->listeners[$eventName][$key], $method)) {
unset($this->listeners[$eventName][$key]);
if (empty($this->listeners[$eventName])) {
unset($this->listeners[$eventName]);
}
unset($this->listenerIds[$eventName][$i]);
if (empty($this->listenerIds[$eventName])) {
unset($this->listenerIds[$eventName]);
}
}
}
}
parent::removeListener($eventName, $listener);
}
public function hasListeners($eventName = null)
{
if (null === $eventName) {
return (bool) count($this->listenerIds) || (bool) count($this->listeners);
}
if (isset($this->listenerIds[$eventName])) {
return true;
}
return parent::hasListeners($eventName);
}
public function getListeners($eventName = null)
{
if (null === $eventName) {
foreach ($this->listenerIds as $serviceEventName => $args) {
$this->lazyLoad($serviceEventName);
}
} else {
$this->lazyLoad($eventName);
}
return parent::getListeners($eventName);
}
public function addSubscriberService($serviceId, $class)
{
foreach ($class::getSubscribedEvents() as $eventName => $params) {
if (is_string($params)) {
$this->listenerIds[$eventName][] = array($serviceId, $params, 0);
} elseif (is_string($params[0])) {
$this->listenerIds[$eventName][] = array($serviceId, $params[0], isset($params[1]) ? $params[1] : 0);
} else {
foreach ($params as $listener) {
$this->listenerIds[$eventName][] = array($serviceId, $listener[0], isset($listener[1]) ? $listener[1] : 0);
}
}
}
}
public function dispatch($eventName, Event $event = null)
{
$this->lazyLoad($eventName);
return parent::dispatch($eventName, $event);
}
public function getContainer()
{
return $this->container;
}
protected function lazyLoad($eventName)
{
if (isset($this->listenerIds[$eventName])) {
foreach ($this->listenerIds[$eventName] as $args) {
list($serviceId, $method, $priority) = $args;
$listener = $this->container->get($serviceId);
$key = $serviceId.'.'.$method;
if (!isset($this->listeners[$eventName][$key])) {
$this->addListener($eventName, array($listener, $method), $priority);
} elseif ($listener !== $this->listeners[$eventName][$key]) {
parent::removeListener($eventName, array($this->listeners[$eventName][$key], $method));
$this->addListener($eventName, array($listener, $method), $priority);
}
$this->listeners[$eventName][$key] = $listener;
}
}
}
}
}
namespace Symfony\Component\HttpKernel\EventListener
{
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class ResponseListener implements EventSubscriberInterface
{
private $charset;
public function __construct($charset)
{
$this->charset = $charset;
}
public function onKernelResponse(FilterResponseEvent $event)
{
if (!$event->isMasterRequest()) {
return;
}
$response = $event->getResponse();
if (null === $response->getCharset()) {
$response->setCharset($this->charset);
}
$response->prepare($event->getRequest());
}
public static function getSubscribedEvents()
{
return array(
KernelEvents::RESPONSE =>'onKernelResponse',
);
}
}
}
namespace Symfony\Component\HttpKernel\EventListener
{
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
class RouterListener implements EventSubscriberInterface
{
private $matcher;
private $context;
private $logger;
private $request;
private $requestStack;
public function __construct($matcher, RequestContext $context = null, LoggerInterface $logger = null, RequestStack $requestStack = null)
{
if (!$matcher instanceof UrlMatcherInterface && !$matcher instanceof RequestMatcherInterface) {
throw new \InvalidArgumentException('Matcher must either implement UrlMatcherInterface or RequestMatcherInterface.');
}
if (null === $context && !$matcher instanceof RequestContextAwareInterface) {
throw new \InvalidArgumentException('You must either pass a RequestContext or the matcher must implement RequestContextAwareInterface.');
}
$this->matcher = $matcher;
$this->context = $context ?: $matcher->getContext();
$this->requestStack = $requestStack;
$this->logger = $logger;
}
public function setRequest(Request $request = null)
{
if (null !== $request && $this->request !== $request) {
$this->context->fromRequest($request);
}
$this->request = $request;
}
public function onKernelFinishRequest(FinishRequestEvent $event)
{
if (null === $this->requestStack) {
return; }
$this->setRequest($this->requestStack->getParentRequest());
}
public function onKernelRequest(GetResponseEvent $event)
{
$request = $event->getRequest();
if (null !== $this->requestStack) {
$this->setRequest($request);
}
if ($request->attributes->has('_controller')) {
return;
}
try {
if ($this->matcher instanceof RequestMatcherInterface) {
$parameters = $this->matcher->matchRequest($request);
} else {
$parameters = $this->matcher->match($request->getPathInfo());
}
if (null !== $this->logger) {
$this->logger->info(sprintf('Matched route "%s" (parameters: %s)', $parameters['_route'], $this->parametersToString($parameters)));
}
$request->attributes->add($parameters);
unset($parameters['_route'], $parameters['_controller']);
$request->attributes->set('_route_params', $parameters);
} catch (ResourceNotFoundException $e) {
$message = sprintf('No route found for "%s %s"', $request->getMethod(), $request->getPathInfo());
if ($referer = $request->headers->get('referer')) {
$message .= sprintf(' (from "%s")', $referer);
}
throw new NotFoundHttpException($message, $e);
} catch (MethodNotAllowedException $e) {
$message = sprintf('No route found for "%s %s": Method Not Allowed (Allow: %s)', $request->getMethod(), $request->getPathInfo(), implode(', ', $e->getAllowedMethods()));
throw new MethodNotAllowedHttpException($e->getAllowedMethods(), $message, $e);
}
}
private function parametersToString(array $parameters)
{
$pieces = array();
foreach ($parameters as $key => $val) {
$pieces[] = sprintf('"%s": "%s"', $key, (is_string($val) ? $val : json_encode($val)));
}
return implode(', ', $pieces);
}
public static function getSubscribedEvents()
{
return array(
KernelEvents::REQUEST => array(array('onKernelRequest', 32)),
KernelEvents::FINISH_REQUEST => array(array('onKernelFinishRequest', 0)),
);
}
}
}
namespace Symfony\Component\HttpKernel\Controller
{
use Symfony\Component\HttpFoundation\Request;
interface ControllerResolverInterface
{
public function getController(Request $request);
public function getArguments(Request $request, $controller);
}
}
namespace Symfony\Component\HttpKernel\Controller
{
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
class ControllerResolver implements ControllerResolverInterface
{
private $logger;
public function __construct(LoggerInterface $logger = null)
{
$this->logger = $logger;
}
public function getController(Request $request)
{
if (!$controller = $request->attributes->get('_controller')) {
if (null !== $this->logger) {
$this->logger->warning('Unable to look for the controller as the "_controller" parameter is missing');
}
return false;
}
if (is_array($controller)) {
return $controller;
}
if (is_object($controller)) {
if (method_exists($controller,'__invoke')) {
return $controller;
}
throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', get_class($controller), $request->getPathInfo()));
}
if (false === strpos($controller,':')) {
if (method_exists($controller,'__invoke')) {
return $this->instantiateController($controller);
} elseif (function_exists($controller)) {
return $controller;
}
}
$callable = $this->createController($controller);
if (!is_callable($callable)) {
throw new \InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', $controller, $request->getPathInfo()));
}
return $callable;
}
public function getArguments(Request $request, $controller)
{
if (is_array($controller)) {
$r = new \ReflectionMethod($controller[0], $controller[1]);
} elseif (is_object($controller) && !$controller instanceof \Closure) {
$r = new \ReflectionObject($controller);
$r = $r->getMethod('__invoke');
} else {
$r = new \ReflectionFunction($controller);
}
return $this->doGetArguments($request, $controller, $r->getParameters());
}
protected function doGetArguments(Request $request, $controller, array $parameters)
{
$attributes = $request->attributes->all();
$arguments = array();
foreach ($parameters as $param) {
if (array_key_exists($param->name, $attributes)) {
$arguments[] = $attributes[$param->name];
} elseif ($param->getClass() && $param->getClass()->isInstance($request)) {
$arguments[] = $request;
} elseif ($param->isDefaultValueAvailable()) {
$arguments[] = $param->getDefaultValue();
} else {
if (is_array($controller)) {
$repr = sprintf('%s::%s()', get_class($controller[0]), $controller[1]);
} elseif (is_object($controller)) {
$repr = get_class($controller);
} else {
$repr = $controller;
}
throw new \RuntimeException(sprintf('Controller "%s" requires that you provide a value for the "$%s" argument (because there is no default value or because there is a non optional argument after this one).', $repr, $param->name));
}
}
return $arguments;
}
protected function createController($controller)
{
if (false === strpos($controller,'::')) {
throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
}
list($class, $method) = explode('::', $controller, 2);
if (!class_exists($class)) {
throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
}
return array($this->instantiateController($class), $method);
}
protected function instantiateController($class)
{
return new $class();
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;
class KernelEvent extends Event
{
private $kernel;
private $request;
private $requestType;
public function __construct(HttpKernelInterface $kernel, Request $request, $requestType)
{
$this->kernel = $kernel;
$this->request = $request;
$this->requestType = $requestType;
}
public function getKernel()
{
return $this->kernel;
}
public function getRequest()
{
return $this->request;
}
public function getRequestType()
{
return $this->requestType;
}
public function isMasterRequest()
{
return HttpKernelInterface::MASTER_REQUEST === $this->requestType;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class FilterControllerEvent extends KernelEvent
{
private $controller;
public function __construct(HttpKernelInterface $kernel, $controller, Request $request, $requestType)
{
parent::__construct($kernel, $request, $requestType);
$this->setController($controller);
}
public function getController()
{
return $this->controller;
}
public function setController($controller)
{
if (!is_callable($controller)) {
throw new \LogicException(sprintf('The controller must be a callable (%s given).', $this->varToString($controller)));
}
$this->controller = $controller;
}
private function varToString($var)
{
if (is_object($var)) {
return sprintf('Object(%s)', get_class($var));
}
if (is_array($var)) {
$a = array();
foreach ($var as $k => $v) {
$a[] = sprintf('%s => %s', $k, $this->varToString($v));
}
return sprintf('Array(%s)', implode(', ', $a));
}
if (is_resource($var)) {
return sprintf('Resource(%s)', get_resource_type($var));
}
if (null === $var) {
return'null';
}
if (false === $var) {
return'false';
}
if (true === $var) {
return'true';
}
return (string) $var;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
class FilterResponseEvent extends KernelEvent
{
private $response;
public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, Response $response)
{
parent::__construct($kernel, $request, $requestType);
$this->setResponse($response);
}
public function getResponse()
{
return $this->response;
}
public function setResponse(Response $response)
{
$this->response = $response;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpFoundation\Response;
class GetResponseEvent extends KernelEvent
{
private $response;
public function getResponse()
{
return $this->response;
}
public function setResponse(Response $response)
{
$this->response = $response;
$this->stopPropagation();
}
public function hasResponse()
{
return null !== $this->response;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class GetResponseForControllerResultEvent extends GetResponseEvent
{
private $controllerResult;
public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, $controllerResult)
{
parent::__construct($kernel, $request, $requestType);
$this->controllerResult = $controllerResult;
}
public function getControllerResult()
{
return $this->controllerResult;
}
public function setControllerResult($controllerResult)
{
$this->controllerResult = $controllerResult;
}
}
}
namespace Symfony\Component\HttpKernel\Event
{
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
class GetResponseForExceptionEvent extends GetResponseEvent
{
private $exception;
public function __construct(HttpKernelInterface $kernel, Request $request, $requestType, \Exception $e)
{
parent::__construct($kernel, $request, $requestType);
$this->setException($e);
}
public function getException()
{
return $this->exception;
}
public function setException(\Exception $exception)
{
$this->exception = $exception;
}
}
}
namespace Symfony\Component\HttpKernel
{
final class KernelEvents
{
const REQUEST ='kernel.request';
const EXCEPTION ='kernel.exception';
const VIEW ='kernel.view';
const CONTROLLER ='kernel.controller';
const RESPONSE ='kernel.response';
const TERMINATE ='kernel.terminate';
const FINISH_REQUEST ='kernel.finish_request';
}
}
namespace Symfony\Component\HttpKernel\Config
{
use Symfony\Component\Config\FileLocator as BaseFileLocator;
use Symfony\Component\HttpKernel\KernelInterface;
class FileLocator extends BaseFileLocator
{
private $kernel;
private $path;
public function __construct(KernelInterface $kernel, $path = null, array $paths = array())
{
$this->kernel = $kernel;
if (null !== $path) {
$this->path = $path;
$paths[] = $path;
}
parent::__construct($paths);
}
public function locate($file, $currentPath = null, $first = true)
{
if (isset($file[0]) &&'@'=== $file[0]) {
return $this->kernel->locateResource($file, $this->path, $first);
}
return parent::locate($file, $currentPath, $first);
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Controller
{
use Symfony\Component\HttpKernel\KernelInterface;
class ControllerNameParser
{
protected $kernel;
public function __construct(KernelInterface $kernel)
{
$this->kernel = $kernel;
}
public function parse($controller)
{
$originalController = $controller;
if (3 !== count($parts = explode(':', $controller))) {
throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid "a:b:c" controller string.', $controller));
}
list($bundle, $controller, $action) = $parts;
$controller = str_replace('/','\\', $controller);
$bundles = array();
try {
$allBundles = $this->kernel->getBundle($bundle, false);
} catch (\InvalidArgumentException $e) {
$message = sprintf('The "%s" (from the _controller value "%s") does not exist or is not enabled in your kernel!',
$bundle,
$originalController
);
if ($alternative = $this->findAlternative($bundle)) {
$message .= sprintf(' Did you mean "%s:%s:%s"?', $alternative, $controller, $action);
}
throw new \InvalidArgumentException($message, 0, $e);
}
foreach ($allBundles as $b) {
$try = $b->getNamespace().'\\Controller\\'.$controller.'Controller';
if (class_exists($try)) {
return $try.'::'.$action.'Action';
}
$bundles[] = $b->getName();
$msg = sprintf('The _controller value "%s:%s:%s" maps to a "%s" class, but this class was not found. Create this class or check the spelling of the class and its namespace.', $bundle, $controller, $action, $try);
}
if (count($bundles) > 1) {
$msg = sprintf('Unable to find controller "%s:%s" in bundles %s.', $bundle, $controller, implode(', ', $bundles));
}
throw new \InvalidArgumentException($msg);
}
public function build($controller)
{
if (0 === preg_match('#^(.*?\\\\Controller\\\\(.+)Controller)::(.+)Action$#', $controller, $match)) {
throw new \InvalidArgumentException(sprintf('The "%s" controller is not a valid "class::method" string.', $controller));
}
$className = $match[1];
$controllerName = $match[2];
$actionName = $match[3];
foreach ($this->kernel->getBundles() as $name => $bundle) {
if (0 !== strpos($className, $bundle->getNamespace())) {
continue;
}
return sprintf('%s:%s:%s', $name, $controllerName, $actionName);
}
throw new \InvalidArgumentException(sprintf('Unable to find a bundle that defines controller "%s".', $controller));
}
private function findAlternative($nonExistentBundleName)
{
$bundleNames = array_map(function ($b) {
return $b->getName();
}, $this->kernel->getBundles());
$alternative = null;
$shortest = null;
foreach ($bundleNames as $bundleName) {
if (false !== strpos($bundleName, $nonExistentBundleName)) {
return $bundleName;
}
$lev = levenshtein($nonExistentBundleName, $bundleName);
if ($lev <= strlen($nonExistentBundleName) / 3 && ($alternative === null || $lev < $shortest)) {
$alternative = $bundleName;
}
}
return $alternative;
}
}
}
namespace Symfony\Bundle\FrameworkBundle\Controller
{
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
class ControllerResolver extends BaseControllerResolver
{
protected $container;
protected $parser;
public function __construct(ContainerInterface $container, ControllerNameParser $parser, LoggerInterface $logger = null)
{
$this->container = $container;
$this->parser = $parser;
parent::__construct($logger);
}
protected function createController($controller)
{
if (false === strpos($controller,'::')) {
$count = substr_count($controller,':');
if (2 == $count) {
$controller = $this->parser->parse($controller);
} elseif (1 == $count) {
list($service, $method) = explode(':', $controller, 2);
return array($this->container->get($service), $method);
} elseif ($this->container->has($controller) && method_exists($service = $this->container->get($controller),'__invoke')) {
return $service;
} else {
throw new \LogicException(sprintf('Unable to parse the controller name "%s".', $controller));
}
}
list($class, $method) = explode('::', $controller, 2);
if (!class_exists($class)) {
throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
}
$controller = $this->instantiateController($class);
if ($controller instanceof ContainerAwareInterface) {
$controller->setContainer($this->container);
}
return array($controller, $method);
}
}
}
namespace Symfony\Component\Security\Http
{
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
class Firewall implements EventSubscriberInterface
{
private $map;
private $dispatcher;
private $exceptionListeners;
public function __construct(FirewallMapInterface $map, EventDispatcherInterface $dispatcher)
{
$this->map = $map;
$this->dispatcher = $dispatcher;
$this->exceptionListeners = new \SplObjectStorage();
}
public function onKernelRequest(GetResponseEvent $event)
{
if (!$event->isMasterRequest()) {
return;
}
list($listeners, $exceptionListener) = $this->map->getListeners($event->getRequest());
if (null !== $exceptionListener) {
$this->exceptionListeners[$event->getRequest()] = $exceptionListener;
$exceptionListener->register($this->dispatcher);
}
foreach ($listeners as $listener) {
$listener->handle($event);
if ($event->hasResponse()) {
break;
}
}
}
public function onKernelFinishRequest(FinishRequestEvent $event)
{
$request = $event->getRequest();
if (isset($this->exceptionListeners[$request])) {
$this->exceptionListeners[$request]->unregister($this->dispatcher);
unset($this->exceptionListeners[$request]);
}
}
public static function getSubscribedEvents()
{
return array(
KernelEvents::REQUEST => array('onKernelRequest', 8),
KernelEvents::FINISH_REQUEST =>'onKernelFinishRequest',
);
}
}
}
namespace Symfony\Component\Security\Core\Authentication\Token\Storage
{
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
interface TokenStorageInterface
{
public function getToken();
public function setToken(TokenInterface $token = null);
}
}
namespace Symfony\Component\Security\Core\Authorization
{
interface AuthorizationCheckerInterface
{
public function isGranted($attributes, $object = null);
}
}
namespace Symfony\Component\Security\Core
{
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
interface SecurityContextInterface extends TokenStorageInterface, AuthorizationCheckerInterface
{
const ACCESS_DENIED_ERROR = Security::ACCESS_DENIED_ERROR;
const AUTHENTICATION_ERROR = Security::AUTHENTICATION_ERROR;
const LAST_USERNAME = Security::LAST_USERNAME;
}
}
namespace Symfony\Component\Security\Core
{
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
class SecurityContext implements SecurityContextInterface
{
private $tokenStorage;
private $authorizationChecker;
public function __construct($tokenStorage, $authorizationChecker, $alwaysAuthenticate = false)
{
$oldSignature = $tokenStorage instanceof AuthenticationManagerInterface && $authorizationChecker instanceof AccessDecisionManagerInterface;
$newSignature = $tokenStorage instanceof TokenStorageInterface && $authorizationChecker instanceof AuthorizationCheckerInterface;
if (!$oldSignature && !$newSignature) {
throw new \BadMethodCallException('Unable to construct SecurityContext, please provide the correct arguments');
}
if ($oldSignature) {
$authenticationManager = $tokenStorage;
$accessDecisionManager = $authorizationChecker;
$tokenStorage = new TokenStorage();
$authorizationChecker = new AuthorizationChecker($tokenStorage, $authenticationManager, $accessDecisionManager, $alwaysAuthenticate);
}
$this->tokenStorage = $tokenStorage;
$this->authorizationChecker = $authorizationChecker;
}
public function getToken()
{
return $this->tokenStorage->getToken();
}
public function setToken(TokenInterface $token = null)
{
return $this->tokenStorage->setToken($token);
}
public function isGranted($attributes, $object = null)
{
return $this->authorizationChecker->isGranted($attributes, $object);
}
}
}
namespace Symfony\Component\Security\Core\User
{
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
interface UserProviderInterface
{
public function loadUserByUsername($username);
public function refreshUser(UserInterface $user);
public function supportsClass($class);
}
}
namespace Symfony\Component\Security\Core\Authentication
{
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
interface AuthenticationManagerInterface
{
public function authenticate(TokenInterface $token);
}
}
namespace Symfony\Component\Security\Core\Authentication
{
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\ProviderNotFoundException;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class AuthenticationProviderManager implements AuthenticationManagerInterface
{
private $providers;
private $eraseCredentials;
private $eventDispatcher;
public function __construct(array $providers, $eraseCredentials = true)
{
if (!$providers) {
throw new \InvalidArgumentException('You must at least add one authentication provider.');
}
$this->providers = $providers;
$this->eraseCredentials = (bool) $eraseCredentials;
}
public function setEventDispatcher(EventDispatcherInterface $dispatcher)
{
$this->eventDispatcher = $dispatcher;
}
public function authenticate(TokenInterface $token)
{
$lastException = null;
$result = null;
foreach ($this->providers as $provider) {
if (!$provider->supports($token)) {
continue;
}
try {
$result = $provider->authenticate($token);
if (null !== $result) {
break;
}
} catch (AccountStatusException $e) {
$e->setToken($token);
throw $e;
} catch (AuthenticationException $e) {
$lastException = $e;
}
}
if (null !== $result) {
if (true === $this->eraseCredentials) {
$result->eraseCredentials();
}
if (null !== $this->eventDispatcher) {
$this->eventDispatcher->dispatch(AuthenticationEvents::AUTHENTICATION_SUCCESS, new AuthenticationEvent($result));
}
return $result;
}
if (null === $lastException) {
$lastException = new ProviderNotFoundException(sprintf('No Authentication Provider found for token of class "%s".', get_class($token)));
}
if (null !== $this->eventDispatcher) {
$this->eventDispatcher->dispatch(AuthenticationEvents::AUTHENTICATION_FAILURE, new AuthenticationFailureEvent($token, $lastException));
}
$lastException->setToken($token);
throw $lastException;
}
}
}
namespace Symfony\Component\Security\Core\Authentication\Token\Storage
{
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class TokenStorage implements TokenStorageInterface
{
private $token;
public function getToken()
{
return $this->token;
}
public function setToken(TokenInterface $token = null)
{
$this->token = $token;
}
}
}
namespace Symfony\Component\Security\Core\Authorization
{
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
interface AccessDecisionManagerInterface
{
public function decide(TokenInterface $token, array $attributes, $object = null);
public function supportsAttribute($attribute);
public function supportsClass($class);
}
}
namespace Symfony\Component\Security\Core\Authorization
{
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
class AccessDecisionManager implements AccessDecisionManagerInterface
{
const STRATEGY_AFFIRMATIVE ='affirmative';
const STRATEGY_CONSENSUS ='consensus';
const STRATEGY_UNANIMOUS ='unanimous';
private $voters;
private $strategy;
private $allowIfAllAbstainDecisions;
private $allowIfEqualGrantedDeniedDecisions;
public function __construct(array $voters, $strategy = self::STRATEGY_AFFIRMATIVE, $allowIfAllAbstainDecisions = false, $allowIfEqualGrantedDeniedDecisions = true)
{
if (!$voters) {
throw new \InvalidArgumentException('You must at least add one voter.');
}
$strategyMethod ='decide'.ucfirst($strategy);
if (!is_callable(array($this, $strategyMethod))) {
throw new \InvalidArgumentException(sprintf('The strategy "%s" is not supported.', $strategy));
}
$this->voters = $voters;
$this->strategy = $strategyMethod;
$this->allowIfAllAbstainDecisions = (bool) $allowIfAllAbstainDecisions;
$this->allowIfEqualGrantedDeniedDecisions = (bool) $allowIfEqualGrantedDeniedDecisions;
}
public function decide(TokenInterface $token, array $attributes, $object = null)
{
return $this->{$this->strategy}($token, $attributes, $object);
}
public function supportsAttribute($attribute)
{
foreach ($this->voters as $voter) {
if ($voter->supportsAttribute($attribute)) {
return true;
}
}
return false;
}
public function supportsClass($class)
{
foreach ($this->voters as $voter) {
if ($voter->supportsClass($class)) {
return true;
}
}
return false;
}
private function decideAffirmative(TokenInterface $token, array $attributes, $object = null)
{
$deny = 0;
foreach ($this->voters as $voter) {
$result = $voter->vote($token, $object, $attributes);
switch ($result) {
case VoterInterface::ACCESS_GRANTED:
return true;
case VoterInterface::ACCESS_DENIED:
++$deny;
break;
default:
break;
}
}
if ($deny > 0) {
return false;
}
return $this->allowIfAllAbstainDecisions;
}
private function decideConsensus(TokenInterface $token, array $attributes, $object = null)
{
$grant = 0;
$deny = 0;
$abstain = 0;
foreach ($this->voters as $voter) {
$result = $voter->vote($token, $object, $attributes);
switch ($result) {
case VoterInterface::ACCESS_GRANTED:
++$grant;
break;
case VoterInterface::ACCESS_DENIED:
++$deny;
break;
default:
++$abstain;
break;
}
}
if ($grant > $deny) {
return true;
}
if ($deny > $grant) {
return false;
}
if ($grant == $deny && $grant != 0) {
return $this->allowIfEqualGrantedDeniedDecisions;
}
return $this->allowIfAllAbstainDecisions;
}
private function decideUnanimous(TokenInterface $token, array $attributes, $object = null)
{
$grant = 0;
foreach ($attributes as $attribute) {
foreach ($this->voters as $voter) {
$result = $voter->vote($token, $object, array($attribute));
switch ($result) {
case VoterInterface::ACCESS_GRANTED:
++$grant;
break;
case VoterInterface::ACCESS_DENIED:
return false;
default:
break;
}
}
}
if ($grant > 0) {
return true;
}
return $this->allowIfAllAbstainDecisions;
}
}
}
namespace Symfony\Component\Security\Core\Authorization
{
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
class AuthorizationChecker implements AuthorizationCheckerInterface
{
private $tokenStorage;
private $accessDecisionManager;
private $authenticationManager;
private $alwaysAuthenticate;
public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, AccessDecisionManagerInterface $accessDecisionManager, $alwaysAuthenticate = false)
{
$this->tokenStorage = $tokenStorage;
$this->authenticationManager = $authenticationManager;
$this->accessDecisionManager = $accessDecisionManager;
$this->alwaysAuthenticate = $alwaysAuthenticate;
}
final public function isGranted($attributes, $object = null)
{
if (null === ($token = $this->tokenStorage->getToken())) {
throw new AuthenticationCredentialsNotFoundException('The token storage contains no authentication token. One possible reason may be that there is no firewall configured for this URL.');
}
if ($this->alwaysAuthenticate || !$token->isAuthenticated()) {
$this->tokenStorage->setToken($token = $this->authenticationManager->authenticate($token));
}
if (!is_array($attributes)) {
$attributes = array($attributes);
}
return $this->accessDecisionManager->decide($token, $attributes, $object);
}
}
}
namespace Symfony\Component\Security\Core\Authorization\Voter
{
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
interface VoterInterface
{
const ACCESS_GRANTED = 1;
const ACCESS_ABSTAIN = 0;
const ACCESS_DENIED = -1;
public function supportsAttribute($attribute);
public function supportsClass($class);
public function vote(TokenInterface $token, $object, array $attributes);
}
}
namespace Symfony\Component\Security\Http
{
use Symfony\Component\HttpFoundation\Request;
interface FirewallMapInterface
{
public function getListeners(Request $request);
}
}
namespace Symfony\Bundle\SecurityBundle\Security
{
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
class FirewallMap implements FirewallMapInterface
{
protected $container;
protected $map;
public function __construct(ContainerInterface $container, array $map)
{
$this->container = $container;
$this->map = $map;
}
public function getListeners(Request $request)
{
foreach ($this->map as $contextId => $requestMatcher) {
if (null === $requestMatcher || $requestMatcher->matches($request)) {
return $this->container->get($contextId)->getContext();
}
}
return array(array(), null);
}
}
}
namespace Symfony\Bundle\SecurityBundle\Security
{
use Symfony\Component\Security\Http\Firewall\ExceptionListener;
class FirewallContext
{
private $listeners;
private $exceptionListener;
public function __construct(array $listeners, ExceptionListener $exceptionListener = null)
{
$this->listeners = $listeners;
$this->exceptionListener = $exceptionListener;
}
public function getContext()
{
return array($this->listeners, $this->exceptionListener);
}
}
}
namespace Symfony\Component\HttpFoundation
{
interface RequestMatcherInterface
{
public function matches(Request $request);
}
}
namespace Symfony\Component\HttpFoundation
{
class RequestMatcher implements RequestMatcherInterface
{
private $path;
private $host;
private $methods = array();
private $ips = array();
private $attributes = array();
private $schemes = array();
public function __construct($path = null, $host = null, $methods = null, $ips = null, array $attributes = array(), $schemes = null)
{
$this->matchPath($path);
$this->matchHost($host);
$this->matchMethod($methods);
$this->matchIps($ips);
$this->matchScheme($schemes);
foreach ($attributes as $k => $v) {
$this->matchAttribute($k, $v);
}
}
public function matchScheme($scheme)
{
$this->schemes = array_map('strtolower', (array) $scheme);
}
public function matchHost($regexp)
{
$this->host = $regexp;
}
public function matchPath($regexp)
{
$this->path = $regexp;
}
public function matchIp($ip)
{
$this->matchIps($ip);
}
public function matchIps($ips)
{
$this->ips = (array) $ips;
}
public function matchMethod($method)
{
$this->methods = array_map('strtoupper', (array) $method);
}
public function matchAttribute($key, $regexp)
{
$this->attributes[$key] = $regexp;
}
public function matches(Request $request)
{
if ($this->schemes && !in_array($request->getScheme(), $this->schemes)) {
return false;
}
if ($this->methods && !in_array($request->getMethod(), $this->methods)) {
return false;
}
foreach ($this->attributes as $key => $pattern) {
if (!preg_match('{'.$pattern.'}', $request->attributes->get($key))) {
return false;
}
}
if (null !== $this->path && !preg_match('{'.$this->path.'}', rawurldecode($request->getPathInfo()))) {
return false;
}
if (null !== $this->host && !preg_match('{'.$this->host.'}i', $request->getHost())) {
return false;
}
if (IpUtils::checkIp($request->getClientIp(), $this->ips)) {
return true;
}
return count($this->ips) === 0;
}
}
}
namespace Twig
{
use Twig\Cache\CacheInterface;
use Twig\Cache\FilesystemCache;
use Twig\Cache\NullCache;
use Twig\Error\Error;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\CoreExtension;
use Twig\Extension\EscaperExtension;
use Twig\Extension\ExtensionInterface;
use Twig\Extension\GlobalsInterface;
use Twig\Extension\InitRuntimeInterface;
use Twig\Extension\OptimizerExtension;
use Twig\Extension\StagingExtension;
use Twig\Loader\ArrayLoader;
use Twig\Loader\ChainLoader;
use Twig\Loader\LoaderInterface;
use Twig\Loader\SourceContextLoaderInterface;
use Twig\Node\ModuleNode;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\RuntimeLoader\RuntimeLoaderInterface;
use Twig\TokenParser\TokenParserInterface;
class Environment
{
const VERSION ='1.42.1';
const VERSION_ID = 14201;
const MAJOR_VERSION = 1;
const MINOR_VERSION = 42;
const RELEASE_VERSION = 1;
const EXTRA_VERSION ='';
protected $charset;
protected $loader;
protected $debug;
protected $autoReload;
protected $cache;
protected $lexer;
protected $parser;
protected $compiler;
protected $baseTemplateClass;
protected $extensions;
protected $parsers;
protected $visitors;
protected $filters;
protected $tests;
protected $functions;
protected $globals;
protected $runtimeInitialized = false;
protected $extensionInitialized = false;
protected $loadedTemplates;
protected $strictVariables;
protected $unaryOperators;
protected $binaryOperators;
protected $templateClassPrefix ='__TwigTemplate_';
protected $functionCallbacks = [];
protected $filterCallbacks = [];
protected $staging;
private $originalCache;
private $bcWriteCacheFile = false;
private $bcGetCacheFilename = false;
private $lastModifiedExtension = 0;
private $extensionsByClass = [];
private $runtimeLoaders = [];
private $runtimes = [];
private $optionsHash;
public function __construct(LoaderInterface $loader = null, $options = [])
{
if (null !== $loader) {
$this->setLoader($loader);
} else {
@trigger_error('Not passing a "Twig\Lodaer\LoaderInterface" as the first constructor argument of "Twig\Environment" is deprecated since version 1.21.', E_USER_DEPRECATED);
}
$options = array_merge(['debug'=> false,'charset'=>'UTF-8','base_template_class'=>'\Twig\Template','strict_variables'=> false,'autoescape'=>'html','cache'=> false,'auto_reload'=> null,'optimizations'=> -1,
], $options);
$this->debug = (bool) $options['debug'];
$this->charset = strtoupper($options['charset']);
$this->baseTemplateClass = $options['base_template_class'];
$this->autoReload = null === $options['auto_reload'] ? $this->debug : (bool) $options['auto_reload'];
$this->strictVariables = (bool) $options['strict_variables'];
$this->setCache($options['cache']);
$this->addExtension(new CoreExtension());
$this->addExtension(new EscaperExtension($options['autoescape']));
$this->addExtension(new OptimizerExtension($options['optimizations']));
$this->staging = new StagingExtension();
if (\is_string($this->originalCache)) {
$r = new \ReflectionMethod($this,'writeCacheFile');
if (__CLASS__ !== $r->getDeclaringClass()->getName()) {
@trigger_error('The Twig\Environment::writeCacheFile method is deprecated since version 1.22 and will be removed in Twig 2.0.', E_USER_DEPRECATED);
$this->bcWriteCacheFile = true;
}
$r = new \ReflectionMethod($this,'getCacheFilename');
if (__CLASS__ !== $r->getDeclaringClass()->getName()) {
@trigger_error('The Twig\Environment::getCacheFilename method is deprecated since version 1.22 and will be removed in Twig 2.0.', E_USER_DEPRECATED);
$this->bcGetCacheFilename = true;
}
}
}
public function getBaseTemplateClass()
{
return $this->baseTemplateClass;
}
public function setBaseTemplateClass($class)
{
$this->baseTemplateClass = $class;
$this->updateOptionsHash();
}
public function enableDebug()
{
$this->debug = true;
$this->updateOptionsHash();
}
public function disableDebug()
{
$this->debug = false;
$this->updateOptionsHash();
}
public function isDebug()
{
return $this->debug;
}
public function enableAutoReload()
{
$this->autoReload = true;
}
public function disableAutoReload()
{
$this->autoReload = false;
}
public function isAutoReload()
{
return $this->autoReload;
}
public function enableStrictVariables()
{
$this->strictVariables = true;
$this->updateOptionsHash();
}
public function disableStrictVariables()
{
$this->strictVariables = false;
$this->updateOptionsHash();
}
public function isStrictVariables()
{
return $this->strictVariables;
}
public function getCache($original = true)
{
return $original ? $this->originalCache : $this->cache;
}
public function setCache($cache)
{
if (\is_string($cache)) {
$this->originalCache = $cache;
$this->cache = new FilesystemCache($cache);
} elseif (false === $cache) {
$this->originalCache = $cache;
$this->cache = new NullCache();
} elseif (null === $cache) {
@trigger_error('Using "null" as the cache strategy is deprecated since version 1.23 and will be removed in Twig 2.0.', E_USER_DEPRECATED);
$this->originalCache = false;
$this->cache = new NullCache();
} elseif ($cache instanceof CacheInterface) {
$this->originalCache = $this->cache = $cache;
} else {
throw new \LogicException(sprintf('Cache can only be a string, false, or a \Twig\Cache\CacheInterface implementation.'));
}
}
public function getCacheFilename($name)
{
@trigger_error(sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
$key = $this->cache->generateKey($name, $this->getTemplateClass($name));
return !$key ? false : $key;
}
public function getTemplateClass($name, $index = null)
{
$key = $this->getLoader()->getCacheKey($name).$this->optionsHash;
return $this->templateClassPrefix.hash('sha256', $key).(null === $index ?'':'___'.$index);
}
public function getTemplateClassPrefix()
{
@trigger_error(sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
return $this->templateClassPrefix;
}
public function render($name, array $context = [])
{
return $this->load($name)->render($context);
}
public function display($name, array $context = [])
{
$this->load($name)->display($context);
}
public function load($name)
{
if ($name instanceof TemplateWrapper) {
return $name;
}
if ($name instanceof Template) {
return new TemplateWrapper($this, $name);
}
return new TemplateWrapper($this, $this->loadTemplate($name));
}
public function loadTemplate($name, $index = null)
{
return $this->loadClass($this->getTemplateClass($name), $name, $index);
}
public function loadClass($cls, $name, $index = null)
{
$mainCls = $cls;
if (null !== $index) {
$cls .='___'.$index;
}
if (isset($this->loadedTemplates[$cls])) {
return $this->loadedTemplates[$cls];
}
if (!class_exists($cls, false)) {
if ($this->bcGetCacheFilename) {
$key = $this->getCacheFilename($name);
} else {
$key = $this->cache->generateKey($name, $mainCls);
}
if (!$this->isAutoReload() || $this->isTemplateFresh($name, $this->cache->getTimestamp($key))) {
$this->cache->load($key);
}
$source = null;
if (!class_exists($cls, false)) {
$loader = $this->getLoader();
if (!$loader instanceof SourceContextLoaderInterface) {
$source = new Source($loader->getSource($name), $name);
} else {
$source = $loader->getSourceContext($name);
}
$content = $this->compileSource($source);
if ($this->bcWriteCacheFile) {
$this->writeCacheFile($key, $content);
} else {
$this->cache->write($key, $content);
$this->cache->load($key);
}
if (!class_exists($mainCls, false)) {
eval('?>'.$content);
}
}
if (!class_exists($cls, false)) {
throw new RuntimeError(sprintf('Failed to load Twig template "%s", index "%s": cache might be corrupted.', $name, $index), -1, $source);
}
}
if (!$this->runtimeInitialized) {
$this->initRuntime();
}
return $this->loadedTemplates[$cls] = new $cls($this);
}
public function createTemplate($template, $name = null)
{
$hash = hash('sha256', $template, false);
if (null !== $name) {
$name = sprintf('%s (string template %s)', $name, $hash);
} else {
$name = sprintf('__string_template__%s', $hash);
}
$loader = new ChainLoader([
new ArrayLoader([$name => $template]),
$current = $this->getLoader(),
]);
$this->setLoader($loader);
try {
$template = new TemplateWrapper($this, $this->loadTemplate($name));
} catch (\Exception $e) {
$this->setLoader($current);
throw $e;
} catch (\Throwable $e) {
$this->setLoader($current);
throw $e;
}
$this->setLoader($current);
return $template;
}
public function isTemplateFresh($name, $time)
{
if (0 === $this->lastModifiedExtension) {
foreach ($this->extensions as $extension) {
$r = new \ReflectionObject($extension);
if (file_exists($r->getFileName()) && ($extensionTime = filemtime($r->getFileName())) > $this->lastModifiedExtension) {
$this->lastModifiedExtension = $extensionTime;
}
}
}
return $this->lastModifiedExtension <= $time && $this->getLoader()->isFresh($name, $time);
}
public function resolveTemplate($names)
{
if (!\is_array($names)) {
$names = [$names];
}
foreach ($names as $name) {
if ($name instanceof Template) {
return $name;
}
if ($name instanceof TemplateWrapper) {
return $name;
}
try {
return $this->loadTemplate($name);
} catch (LoaderError $e) {
if (1 === \count($names)) {
throw $e;
}
}
}
throw new LoaderError(sprintf('Unable to find one of the following templates: "%s".', implode('", "', $names)));
}
public function clearTemplateCache()
{
@trigger_error(sprintf('The %s method is deprecated since version 1.18.3 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
$this->loadedTemplates = [];
}
public function clearCacheFiles()
{
@trigger_error(sprintf('The %s method is deprecated since version 1.22 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
if (\is_string($this->originalCache)) {
foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->originalCache), \RecursiveIteratorIterator::LEAVES_ONLY) as $file) {
if ($file->isFile()) {
@unlink($file->getPathname());
}
}
}
}
public function getLexer()
{
@trigger_error(sprintf('The %s() method is deprecated since version 1.25 and will be removed in 2.0.', __FUNCTION__), E_USER_DEPRECATED);
if (null === $this->lexer) {
$this->lexer = new Lexer($this);
}
return $this->lexer;
}
public function setLexer(\Twig_LexerInterface $lexer)
{
$this->lexer = $lexer;
}
public function tokenize($source, $name = null)
{
if (!$source instanceof Source) {
@trigger_error(sprintf('Passing a string as the $source argument of %s() is deprecated since version 1.27. Pass a Twig\Source instance instead.', __METHOD__), E_USER_DEPRECATED);
$source = new Source($source, $name);
}
if (null === $this->lexer) {
$this->lexer = new Lexer($this);
}
return $this->lexer->tokenize($source);
}
public function getParser()
{
@trigger_error(sprintf('The %s() method is deprecated since version 1.25 and will be removed in 2.0.', __FUNCTION__), E_USER_DEPRECATED);
if (null === $this->parser) {
$this->parser = new Parser($this);
}
return $this->parser;
}
public function setParser(\Twig_ParserInterface $parser)
{
$this->parser = $parser;
}
public function parse(TokenStream $stream)
{
if (null === $this->parser) {
$this->parser = new Parser($this);
}
return $this->parser->parse($stream);
}
public function getCompiler()
{
@trigger_error(sprintf('The %s() method is deprecated since version 1.25 and will be removed in 2.0.', __FUNCTION__), E_USER_DEPRECATED);
if (null === $this->compiler) {
$this->compiler = new Compiler($this);
}
return $this->compiler;
}
public function setCompiler(\Twig_CompilerInterface $compiler)
{
$this->compiler = $compiler;
}
public function compile(\Twig_NodeInterface $node)
{
if (null === $this->compiler) {
$this->compiler = new Compiler($this);
}
return $this->compiler->compile($node)->getSource();
}
public function compileSource($source, $name = null)
{
if (!$source instanceof Source) {
@trigger_error(sprintf('Passing a string as the $source argument of %s() is deprecated since version 1.27. Pass a Twig\Source instance instead.', __METHOD__), E_USER_DEPRECATED);
$source = new Source($source, $name);
}
try {
return $this->compile($this->parse($this->tokenize($source)));
} catch (Error $e) {
$e->setSourceContext($source);
throw $e;
} catch (\Exception $e) {
throw new SyntaxError(sprintf('An exception has been thrown during the compilation of a template ("%s").', $e->getMessage()), -1, $source, $e);
}
}
public function setLoader(LoaderInterface $loader)
{
if (!$loader instanceof SourceContextLoaderInterface && 0 !== strpos(\get_class($loader),'Mock_')) {
@trigger_error(sprintf('Twig loader "%s" should implement Twig\Loader\SourceContextLoaderInterface since version 1.27.', \get_class($loader)), E_USER_DEPRECATED);
}
$this->loader = $loader;
}
public function getLoader()
{
if (null === $this->loader) {
throw new \LogicException('You must set a loader first.');
}
return $this->loader;
}
public function setCharset($charset)
{
$this->charset = strtoupper($charset);
}
public function getCharset()
{
return $this->charset;
}
public function initRuntime()
{
$this->runtimeInitialized = true;
foreach ($this->getExtensions() as $name => $extension) {
if (!$extension instanceof InitRuntimeInterface) {
$m = new \ReflectionMethod($extension,'initRuntime');
$parentClass = $m->getDeclaringClass()->getName();
if ('Twig_Extension'!== $parentClass &&'Twig\Extension\AbstractExtension'!== $parentClass) {
@trigger_error(sprintf('Defining the initRuntime() method in the "%s" extension is deprecated since version 1.23. Use the `needs_environment` option to get the \Twig_Environment instance in filters, functions, or tests; or explicitly implement Twig\Extension\InitRuntimeInterface if needed (not recommended).', $name), E_USER_DEPRECATED);
}
}
$extension->initRuntime($this);
}
}
public function hasExtension($class)
{
$class = ltrim($class,'\\');
if (!isset($this->extensionsByClass[$class]) && class_exists($class, false)) {
$class = new \ReflectionClass($class);
$class = $class->name;
}
if (isset($this->extensions[$class])) {
if ($class !== \get_class($this->extensions[$class])) {
@trigger_error(sprintf('Referencing the "%s" extension by its name (defined by getName()) is deprecated since 1.26 and will be removed in Twig 2.0. Use the Fully Qualified Extension Class Name instead.', $class), E_USER_DEPRECATED);
}
return true;
}
return isset($this->extensionsByClass[$class]);
}
public function addRuntimeLoader(RuntimeLoaderInterface $loader)
{
$this->runtimeLoaders[] = $loader;
}
public function getExtension($class)
{
$class = ltrim($class,'\\');
if (!isset($this->extensionsByClass[$class]) && class_exists($class, false)) {
$class = new \ReflectionClass($class);
$class = $class->name;
}
if (isset($this->extensions[$class])) {
if ($class !== \get_class($this->extensions[$class])) {
@trigger_error(sprintf('Referencing the "%s" extension by its name (defined by getName()) is deprecated since 1.26 and will be removed in Twig 2.0. Use the Fully Qualified Extension Class Name instead.', $class), E_USER_DEPRECATED);
}
return $this->extensions[$class];
}
if (!isset($this->extensionsByClass[$class])) {
throw new RuntimeError(sprintf('The "%s" extension is not enabled.', $class));
}
return $this->extensionsByClass[$class];
}
public function getRuntime($class)
{
if (isset($this->runtimes[$class])) {
return $this->runtimes[$class];
}
foreach ($this->runtimeLoaders as $loader) {
if (null !== $runtime = $loader->load($class)) {
return $this->runtimes[$class] = $runtime;
}
}
throw new RuntimeError(sprintf('Unable to load the "%s" runtime.', $class));
}
public function addExtension(ExtensionInterface $extension)
{
if ($this->extensionInitialized) {
throw new \LogicException(sprintf('Unable to register extension "%s" as extensions have already been initialized.', $extension->getName()));
}
$class = \get_class($extension);
if ($class !== $extension->getName()) {
if (isset($this->extensions[$extension->getName()])) {
unset($this->extensions[$extension->getName()], $this->extensionsByClass[$class]);
@trigger_error(sprintf('The possibility to register the same extension twice ("%s") is deprecated since version 1.23 and will be removed in Twig 2.0. Use proper PHP inheritance instead.', $extension->getName()), E_USER_DEPRECATED);
}
}
$this->lastModifiedExtension = 0;
$this->extensionsByClass[$class] = $extension;
$this->extensions[$extension->getName()] = $extension;
$this->updateOptionsHash();
}
public function removeExtension($name)
{
@trigger_error(sprintf('The %s method is deprecated since version 1.12 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
if ($this->extensionInitialized) {
throw new \LogicException(sprintf('Unable to remove extension "%s" as extensions have already been initialized.', $name));
}
$class = ltrim($name,'\\');
if (!isset($this->extensionsByClass[$class]) && class_exists($class, false)) {
$class = new \ReflectionClass($class);
$class = $class->name;
}
if (isset($this->extensions[$class])) {
if ($class !== \get_class($this->extensions[$class])) {
@trigger_error(sprintf('Referencing the "%s" extension by its name (defined by getName()) is deprecated since 1.26 and will be removed in Twig 2.0. Use the Fully Qualified Extension Class Name instead.', $class), E_USER_DEPRECATED);
}
unset($this->extensions[$class]);
}
unset($this->extensions[$class]);
$this->updateOptionsHash();
}
public function setExtensions(array $extensions)
{
foreach ($extensions as $extension) {
$this->addExtension($extension);
}
}
public function getExtensions()
{
return $this->extensions;
}
public function addTokenParser(TokenParserInterface $parser)
{
if ($this->extensionInitialized) {
throw new \LogicException('Unable to add a token parser as extensions have already been initialized.');
}
$this->staging->addTokenParser($parser);
}
public function getTokenParsers()
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
return $this->parsers;
}
public function getTags()
{
$tags = [];
foreach ($this->getTokenParsers()->getParsers() as $parser) {
if ($parser instanceof TokenParserInterface) {
$tags[$parser->getTag()] = $parser;
}
}
return $tags;
}
public function addNodeVisitor(NodeVisitorInterface $visitor)
{
if ($this->extensionInitialized) {
throw new \LogicException('Unable to add a node visitor as extensions have already been initialized.');
}
$this->staging->addNodeVisitor($visitor);
}
public function getNodeVisitors()
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
return $this->visitors;
}
public function addFilter($name, $filter = null)
{
if (!$name instanceof TwigFilter && !($filter instanceof TwigFilter || $filter instanceof \Twig_FilterInterface)) {
throw new \LogicException('A filter must be an instance of \Twig_FilterInterface or \Twig_SimpleFilter.');
}
if ($name instanceof TwigFilter) {
$filter = $name;
$name = $filter->getName();
} else {
@trigger_error(sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twig_SimpleFilter" instead when defining filter "%s".', __METHOD__, $name), E_USER_DEPRECATED);
}
if ($this->extensionInitialized) {
throw new \LogicException(sprintf('Unable to add filter "%s" as extensions have already been initialized.', $name));
}
$this->staging->addFilter($name, $filter);
}
public function getFilter($name)
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
if (isset($this->filters[$name])) {
return $this->filters[$name];
}
foreach ($this->filters as $pattern => $filter) {
$pattern = str_replace('\\*','(.*?)', preg_quote($pattern,'#'), $count);
if ($count) {
if (preg_match('#^'.$pattern.'$#', $name, $matches)) {
array_shift($matches);
$filter->setArguments($matches);
return $filter;
}
}
}
foreach ($this->filterCallbacks as $callback) {
if (false !== $filter = \call_user_func($callback, $name)) {
return $filter;
}
}
return false;
}
public function registerUndefinedFilterCallback($callable)
{
$this->filterCallbacks[] = $callable;
}
public function getFilters()
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
return $this->filters;
}
public function addTest($name, $test = null)
{
if (!$name instanceof TwigTest && !($test instanceof TwigTest || $test instanceof \Twig_TestInterface)) {
throw new \LogicException('A test must be an instance of \Twig_TestInterface or \Twig_SimpleTest.');
}
if ($name instanceof TwigTest) {
$test = $name;
$name = $test->getName();
} else {
@trigger_error(sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twig_SimpleTest" instead when defining test "%s".', __METHOD__, $name), E_USER_DEPRECATED);
}
if ($this->extensionInitialized) {
throw new \LogicException(sprintf('Unable to add test "%s" as extensions have already been initialized.', $name));
}
$this->staging->addTest($name, $test);
}
public function getTests()
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
return $this->tests;
}
public function getTest($name)
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
if (isset($this->tests[$name])) {
return $this->tests[$name];
}
foreach ($this->tests as $pattern => $test) {
$pattern = str_replace('\\*','(.*?)', preg_quote($pattern,'#'), $count);
if ($count) {
if (preg_match('#^'.$pattern.'$#', $name, $matches)) {
array_shift($matches);
$test->setArguments($matches);
return $test;
}
}
}
return false;
}
public function addFunction($name, $function = null)
{
if (!$name instanceof TwigFunction && !($function instanceof TwigFunction || $function instanceof \Twig_FunctionInterface)) {
throw new \LogicException('A function must be an instance of \Twig_FunctionInterface or \Twig_SimpleFunction.');
}
if ($name instanceof TwigFunction) {
$function = $name;
$name = $function->getName();
} else {
@trigger_error(sprintf('Passing a name as a first argument to the %s method is deprecated since version 1.21. Pass an instance of "Twig_SimpleFunction" instead when defining function "%s".', __METHOD__, $name), E_USER_DEPRECATED);
}
if ($this->extensionInitialized) {
throw new \LogicException(sprintf('Unable to add function "%s" as extensions have already been initialized.', $name));
}
$this->staging->addFunction($name, $function);
}
public function getFunction($name)
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
if (isset($this->functions[$name])) {
return $this->functions[$name];
}
foreach ($this->functions as $pattern => $function) {
$pattern = str_replace('\\*','(.*?)', preg_quote($pattern,'#'), $count);
if ($count) {
if (preg_match('#^'.$pattern.'$#', $name, $matches)) {
array_shift($matches);
$function->setArguments($matches);
return $function;
}
}
}
foreach ($this->functionCallbacks as $callback) {
if (false !== $function = \call_user_func($callback, $name)) {
return $function;
}
}
return false;
}
public function registerUndefinedFunctionCallback($callable)
{
$this->functionCallbacks[] = $callable;
}
public function getFunctions()
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
return $this->functions;
}
public function addGlobal($name, $value)
{
if ($this->extensionInitialized || $this->runtimeInitialized) {
if (null === $this->globals) {
$this->globals = $this->initGlobals();
}
if (!\array_key_exists($name, $this->globals)) {
@trigger_error(sprintf('Registering global variable "%s" at runtime or when the extensions have already been initialized is deprecated since version 1.21.', $name), E_USER_DEPRECATED);
}
}
if ($this->extensionInitialized || $this->runtimeInitialized) {
$this->globals[$name] = $value;
} else {
$this->staging->addGlobal($name, $value);
}
}
public function getGlobals()
{
if (!$this->runtimeInitialized && !$this->extensionInitialized) {
return $this->initGlobals();
}
if (null === $this->globals) {
$this->globals = $this->initGlobals();
}
return $this->globals;
}
public function mergeGlobals(array $context)
{
foreach ($this->getGlobals() as $key => $value) {
if (!\array_key_exists($key, $context)) {
$context[$key] = $value;
}
}
return $context;
}
public function getUnaryOperators()
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
return $this->unaryOperators;
}
public function getBinaryOperators()
{
if (!$this->extensionInitialized) {
$this->initExtensions();
}
return $this->binaryOperators;
}
public function computeAlternatives($name, $items)
{
@trigger_error(sprintf('The %s method is deprecated since version 1.23 and will be removed in Twig 2.0.', __METHOD__), E_USER_DEPRECATED);
return SyntaxError::computeAlternatives($name, $items);
}
protected function initGlobals()
{
$globals = [];
foreach ($this->extensions as $name => $extension) {
if (!$extension instanceof GlobalsInterface) {
$m = new \ReflectionMethod($extension,'getGlobals');
$parentClass = $m->getDeclaringClass()->getName();
if ('Twig_Extension'!== $parentClass &&'Twig\Extension\AbstractExtension'!== $parentClass) {
@trigger_error(sprintf('Defining the getGlobals() method in the "%s" extension without explicitly implementing Twig\Extension\GlobalsInterface is deprecated since version 1.23.', $name), E_USER_DEPRECATED);
}
}
$extGlob = $extension->getGlobals();
if (!\is_array($extGlob)) {
throw new \UnexpectedValueException(sprintf('"%s::getGlobals()" must return an array of globals.', \get_class($extension)));
}
$globals[] = $extGlob;
}
$globals[] = $this->staging->getGlobals();
return \call_user_func_array('array_merge', $globals);
}
protected function initExtensions()
{
if ($this->extensionInitialized) {
return;
}
$this->parsers = new \Twig_TokenParserBroker([], [], false);
$this->filters = [];
$this->functions = [];
$this->tests = [];
$this->visitors = [];
$this->unaryOperators = [];
$this->binaryOperators = [];
foreach ($this->extensions as $extension) {
$this->initExtension($extension);
}
$this->initExtension($this->staging);
$this->extensionInitialized = true;
}
protected function initExtension(ExtensionInterface $extension)
{
foreach ($extension->getFilters() as $name => $filter) {
if ($filter instanceof TwigFilter) {
$name = $filter->getName();
} else {
@trigger_error(sprintf('Using an instance of "%s" for filter "%s" is deprecated since version 1.21. Use \Twig_SimpleFilter instead.', \get_class($filter), $name), E_USER_DEPRECATED);
}
$this->filters[$name] = $filter;
}
foreach ($extension->getFunctions() as $name => $function) {
if ($function instanceof TwigFunction) {
$name = $function->getName();
} else {
@trigger_error(sprintf('Using an instance of "%s" for function "%s" is deprecated since version 1.21. Use \Twig_SimpleFunction instead.', \get_class($function), $name), E_USER_DEPRECATED);
}
$this->functions[$name] = $function;
}
foreach ($extension->getTests() as $name => $test) {
if ($test instanceof TwigTest) {
$name = $test->getName();
} else {
@trigger_error(sprintf('Using an instance of "%s" for test "%s" is deprecated since version 1.21. Use \Twig_SimpleTest instead.', \get_class($test), $name), E_USER_DEPRECATED);
}
$this->tests[$name] = $test;
}
foreach ($extension->getTokenParsers() as $parser) {
if ($parser instanceof TokenParserInterface) {
$this->parsers->addTokenParser($parser);
} elseif ($parser instanceof \Twig_TokenParserBrokerInterface) {
@trigger_error('Registering a \Twig_TokenParserBrokerInterface instance is deprecated since version 1.21.', E_USER_DEPRECATED);
$this->parsers->addTokenParserBroker($parser);
} else {
throw new \LogicException('getTokenParsers() must return an array of \Twig_TokenParserInterface or \Twig_TokenParserBrokerInterface instances.');
}
}
foreach ($extension->getNodeVisitors() as $visitor) {
$this->visitors[] = $visitor;
}
if ($operators = $extension->getOperators()) {
if (!\is_array($operators)) {
throw new \InvalidArgumentException(sprintf('"%s::getOperators()" must return an array with operators, got "%s".', \get_class($extension), \is_object($operators) ? \get_class($operators) : \gettype($operators).(\is_resource($operators) ?'':'#'.$operators)));
}
if (2 !== \count($operators)) {
throw new \InvalidArgumentException(sprintf('"%s::getOperators()" must return an array of 2 elements, got %d.', \get_class($extension), \count($operators)));
}
$this->unaryOperators = array_merge($this->unaryOperators, $operators[0]);
$this->binaryOperators = array_merge($this->binaryOperators, $operators[1]);
}
}
protected function writeCacheFile($file, $content)
{
$this->cache->write($file, $content);
}
private function updateOptionsHash()
{
$hashParts = array_merge(
array_keys($this->extensions),
[
(int) \function_exists('twig_template_get_attributes'),
PHP_MAJOR_VERSION,
PHP_MINOR_VERSION,
self::VERSION,
(int) $this->debug,
$this->baseTemplateClass,
(int) $this->strictVariables,
]
);
$this->optionsHash = implode(':', $hashParts);
}
}
class_alias('Twig\Environment','Twig_Environment');
}
namespace Twig\Extension
{
use Twig\Environment;
use Twig\NodeVisitor\NodeVisitorInterface;
use Twig\TokenParser\TokenParserInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
interface ExtensionInterface
{
public function initRuntime(Environment $environment);
public function getTokenParsers();
public function getNodeVisitors();
public function getFilters();
public function getTests();
public function getFunctions();
public function getOperators();
public function getGlobals();
public function getName();
}
class_alias('Twig\Extension\ExtensionInterface','Twig_ExtensionInterface');
class_exists('Twig\Environment');
}
namespace Twig\Extension
{
use Twig\Environment;
abstract class AbstractExtension implements ExtensionInterface
{
public function initRuntime(Environment $environment)
{
}
public function getTokenParsers()
{
return [];
}
public function getNodeVisitors()
{
return [];
}
public function getFilters()
{
return [];
}
public function getTests()
{
return [];
}
public function getFunctions()
{
return [];
}
public function getOperators()
{
return [];
}
public function getGlobals()
{
return [];
}
public function getName()
{
return \get_class($this);
}
}
class_alias('Twig\Extension\AbstractExtension','Twig_Extension');
}
namespace Twig\Extension {
use Twig\ExpressionParser;
use Twig\TokenParser\ApplyTokenParser;
use Twig\TokenParser\BlockTokenParser;
use Twig\TokenParser\DeprecatedTokenParser;
use Twig\TokenParser\DoTokenParser;
use Twig\TokenParser\EmbedTokenParser;
use Twig\TokenParser\ExtendsTokenParser;
use Twig\TokenParser\FilterTokenParser;
use Twig\TokenParser\FlushTokenParser;
use Twig\TokenParser\ForTokenParser;
use Twig\TokenParser\FromTokenParser;
use Twig\TokenParser\IfTokenParser;
use Twig\TokenParser\ImportTokenParser;
use Twig\TokenParser\IncludeTokenParser;
use Twig\TokenParser\MacroTokenParser;
use Twig\TokenParser\SetTokenParser;
use Twig\TokenParser\SpacelessTokenParser;
use Twig\TokenParser\UseTokenParser;
use Twig\TokenParser\WithTokenParser;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
class CoreExtension extends AbstractExtension
{
protected $dateFormats = ['F j, Y H:i','%d days'];
protected $numberFormat = [0,'.',','];
protected $timezone = null;
protected $escapers = [];
public function setEscaper($strategy, $callable)
{
$this->escapers[$strategy] = $callable;
}
public function getEscapers()
{
return $this->escapers;
}
public function setDateFormat($format = null, $dateIntervalFormat = null)
{
if (null !== $format) {
$this->dateFormats[0] = $format;
}
if (null !== $dateIntervalFormat) {
$this->dateFormats[1] = $dateIntervalFormat;
}
}
public function getDateFormat()
{
return $this->dateFormats;
}
public function setTimezone($timezone)
{
$this->timezone = $timezone instanceof \DateTimeZone ? $timezone : new \DateTimeZone($timezone);
}
public function getTimezone()
{
if (null === $this->timezone) {
$this->timezone = new \DateTimeZone(date_default_timezone_get());
}
return $this->timezone;
}
public function setNumberFormat($decimal, $decimalPoint, $thousandSep)
{
$this->numberFormat = [$decimal, $decimalPoint, $thousandSep];
}
public function getNumberFormat()
{
return $this->numberFormat;
}
public function getTokenParsers()
{
return [
new ApplyTokenParser(),
new ForTokenParser(),
new IfTokenParser(),
new ExtendsTokenParser(),
new IncludeTokenParser(),
new BlockTokenParser(),
new UseTokenParser(),
new FilterTokenParser(),
new MacroTokenParser(),
new ImportTokenParser(),
new FromTokenParser(),
new SetTokenParser(),
new SpacelessTokenParser(),
new FlushTokenParser(),
new DoTokenParser(),
new EmbedTokenParser(),
new WithTokenParser(),
new DeprecatedTokenParser(),
];
}
public function getFilters()
{
$filters = [
new TwigFilter('date','twig_date_format_filter', ['needs_environment'=> true]),
new TwigFilter('date_modify','twig_date_modify_filter', ['needs_environment'=> true]),
new TwigFilter('format','sprintf'),
new TwigFilter('replace','twig_replace_filter'),
new TwigFilter('number_format','twig_number_format_filter', ['needs_environment'=> true]),
new TwigFilter('abs','abs'),
new TwigFilter('round','twig_round'),
new TwigFilter('url_encode','twig_urlencode_filter'),
new TwigFilter('json_encode','twig_jsonencode_filter'),
new TwigFilter('convert_encoding','twig_convert_encoding'),
new TwigFilter('title','twig_title_string_filter', ['needs_environment'=> true]),
new TwigFilter('capitalize','twig_capitalize_string_filter', ['needs_environment'=> true]),
new TwigFilter('upper','strtoupper'),
new TwigFilter('lower','strtolower'),
new TwigFilter('striptags','strip_tags'),
new TwigFilter('trim','twig_trim_filter'),
new TwigFilter('nl2br','nl2br', ['pre_escape'=>'html','is_safe'=> ['html']]),
new TwigFilter('spaceless','twig_spaceless', ['is_safe'=> ['html']]),
new TwigFilter('join','twig_join_filter'),
new TwigFilter('split','twig_split_filter', ['needs_environment'=> true]),
new TwigFilter('sort','twig_sort_filter'),
new TwigFilter('merge','twig_array_merge'),
new TwigFilter('batch','twig_array_batch'),
new TwigFilter('filter','twig_array_filter'),
new TwigFilter('map','twig_array_map'),
new TwigFilter('reduce','twig_array_reduce'),
new TwigFilter('reverse','twig_reverse_filter', ['needs_environment'=> true]),
new TwigFilter('length','twig_length_filter', ['needs_environment'=> true]),
new TwigFilter('slice','twig_slice', ['needs_environment'=> true]),
new TwigFilter('first','twig_first', ['needs_environment'=> true]),
new TwigFilter('last','twig_last', ['needs_environment'=> true]),
new TwigFilter('default','_twig_default_filter', ['node_class'=>'\Twig\Node\Expression\Filter\DefaultFilter']),
new TwigFilter('keys','twig_get_array_keys_filter'),
new TwigFilter('escape','twig_escape_filter', ['needs_environment'=> true,'is_safe_callback'=>'twig_escape_filter_is_safe']),
new TwigFilter('e','twig_escape_filter', ['needs_environment'=> true,'is_safe_callback'=>'twig_escape_filter_is_safe']),
];
if (\function_exists('mb_get_info')) {
$filters[] = new TwigFilter('upper','twig_upper_filter', ['needs_environment'=> true]);
$filters[] = new TwigFilter('lower','twig_lower_filter', ['needs_environment'=> true]);
}
return $filters;
}
public function getFunctions()
{
return [
new TwigFunction('max','max'),
new TwigFunction('min','min'),
new TwigFunction('range','range'),
new TwigFunction('constant','twig_constant'),
new TwigFunction('cycle','twig_cycle'),
new TwigFunction('random','twig_random', ['needs_environment'=> true]),
new TwigFunction('date','twig_date_converter', ['needs_environment'=> true]),
new TwigFunction('include','twig_include', ['needs_environment'=> true,'needs_context'=> true,'is_safe'=> ['all']]),
new TwigFunction('source','twig_source', ['needs_environment'=> true,'is_safe'=> ['all']]),
];
}
public function getTests()
{
return [
new TwigTest('even', null, ['node_class'=>'\Twig\Node\Expression\Test\EvenTest']),
new TwigTest('odd', null, ['node_class'=>'\Twig\Node\Expression\Test\OddTest']),
new TwigTest('defined', null, ['node_class'=>'\Twig\Node\Expression\Test\DefinedTest']),
new TwigTest('sameas', null, ['node_class'=>'\Twig\Node\Expression\Test\SameasTest','deprecated'=>'1.21','alternative'=>'same as']),
new TwigTest('same as', null, ['node_class'=>'\Twig\Node\Expression\Test\SameasTest']),
new TwigTest('none', null, ['node_class'=>'\Twig\Node\Expression\Test\NullTest']),
new TwigTest('null', null, ['node_class'=>'\Twig\Node\Expression\Test\NullTest']),
new TwigTest('divisibleby', null, ['node_class'=>'\Twig\Node\Expression\Test\DivisiblebyTest','deprecated'=>'1.21','alternative'=>'divisible by']),
new TwigTest('divisible by', null, ['node_class'=>'\Twig\Node\Expression\Test\DivisiblebyTest']),
new TwigTest('constant', null, ['node_class'=>'\Twig\Node\Expression\Test\ConstantTest']),
new TwigTest('empty','twig_test_empty'),
new TwigTest('iterable','twig_test_iterable'),
];
}
public function getOperators()
{
return [
['not'=> ['precedence'=> 50,'class'=>'\Twig\Node\Expression\Unary\NotUnary'],'-'=> ['precedence'=> 500,'class'=>'\Twig\Node\Expression\Unary\NegUnary'],'+'=> ['precedence'=> 500,'class'=>'\Twig\Node\Expression\Unary\PosUnary'],
],
['or'=> ['precedence'=> 10,'class'=>'\Twig\Node\Expression\Binary\OrBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'and'=> ['precedence'=> 15,'class'=>'\Twig\Node\Expression\Binary\AndBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'b-or'=> ['precedence'=> 16,'class'=>'\Twig\Node\Expression\Binary\BitwiseOrBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'b-xor'=> ['precedence'=> 17,'class'=>'\Twig\Node\Expression\Binary\BitwiseXorBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'b-and'=> ['precedence'=> 18,'class'=>'\Twig\Node\Expression\Binary\BitwiseAndBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'=='=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\EqualBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'!='=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\NotEqualBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'<'=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\LessBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'>'=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\GreaterBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'>='=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\GreaterEqualBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'<='=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\LessEqualBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'not in'=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\NotInBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'in'=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\InBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'matches'=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\MatchesBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'starts with'=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\StartsWithBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'ends with'=> ['precedence'=> 20,'class'=>'\Twig\Node\Expression\Binary\EndsWithBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'..'=> ['precedence'=> 25,'class'=>'\Twig\Node\Expression\Binary\RangeBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'+'=> ['precedence'=> 30,'class'=>'\Twig\Node\Expression\Binary\AddBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'-'=> ['precedence'=> 30,'class'=>'\Twig\Node\Expression\Binary\SubBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'~'=> ['precedence'=> 40,'class'=>'\Twig\Node\Expression\Binary\ConcatBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'*'=> ['precedence'=> 60,'class'=>'\Twig\Node\Expression\Binary\MulBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'/'=> ['precedence'=> 60,'class'=>'\Twig\Node\Expression\Binary\DivBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'//'=> ['precedence'=> 60,'class'=>'\Twig\Node\Expression\Binary\FloorDivBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'%'=> ['precedence'=> 60,'class'=>'\Twig\Node\Expression\Binary\ModBinary','associativity'=> ExpressionParser::OPERATOR_LEFT],'is'=> ['precedence'=> 100,'associativity'=> ExpressionParser::OPERATOR_LEFT],'is not'=> ['precedence'=> 100,'associativity'=> ExpressionParser::OPERATOR_LEFT],'**'=> ['precedence'=> 200,'class'=>'\Twig\Node\Expression\Binary\PowerBinary','associativity'=> ExpressionParser::OPERATOR_RIGHT],'??'=> ['precedence'=> 300,'class'=>'\Twig\Node\Expression\NullCoalesceExpression','associativity'=> ExpressionParser::OPERATOR_RIGHT],
],
];
}
public function getName()
{
return'core';
}
}
class_alias('Twig\Extension\CoreExtension','Twig_Extension_Core');
}
namespace {
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Loader\SourceContextLoaderInterface;
use Twig\Markup;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
function twig_cycle($values, $position)
{
if (!\is_array($values) && !$values instanceof \ArrayAccess) {
return $values;
}
return $values[$position % \count($values)];
}
function twig_random(Environment $env, $values = null, $max = null)
{
if (null === $values) {
return null === $max ? mt_rand() : mt_rand(0, $max);
}
if (\is_int($values) || \is_float($values)) {
if (null === $max) {
if ($values < 0) {
$max = 0;
$min = $values;
} else {
$max = $values;
$min = 0;
}
} else {
$min = $values;
$max = $max;
}
return mt_rand($min, $max);
}
if (\is_string($values)) {
if (''=== $values) {
return'';
}
if (null !== $charset = $env->getCharset()) {
if ('UTF-8'!== $charset) {
$values = twig_convert_encoding($values,'UTF-8', $charset);
}
$values = preg_split('/(?<!^)(?!$)/u', $values);
if ('UTF-8'!== $charset) {
foreach ($values as $i => $value) {
$values[$i] = twig_convert_encoding($value, $charset,'UTF-8');
}
}
} else {
return $values[mt_rand(0, \strlen($values) - 1)];
}
}
if (!twig_test_iterable($values)) {
return $values;
}
$values = twig_to_array($values);
if (0 === \count($values)) {
throw new RuntimeError('The random function cannot pick from an empty array.');
}
return $values[array_rand($values, 1)];
}
function twig_date_format_filter(Environment $env, $date, $format = null, $timezone = null)
{
if (null === $format) {
$formats = $env->getExtension('\Twig\Extension\CoreExtension')->getDateFormat();
$format = $date instanceof \DateInterval ? $formats[1] : $formats[0];
}
if ($date instanceof \DateInterval) {
return $date->format($format);
}
return twig_date_converter($env, $date, $timezone)->format($format);
}
function twig_date_modify_filter(Environment $env, $date, $modifier)
{
$date = twig_date_converter($env, $date, false);
$resultDate = $date->modify($modifier);
return null === $resultDate ? $date : $resultDate;
}
function twig_date_converter(Environment $env, $date = null, $timezone = null)
{
if (false !== $timezone) {
if (null === $timezone) {
$timezone = $env->getExtension('\Twig\Extension\CoreExtension')->getTimezone();
} elseif (!$timezone instanceof \DateTimeZone) {
$timezone = new \DateTimeZone($timezone);
}
}
if ($date instanceof \DateTimeImmutable) {
return false !== $timezone ? $date->setTimezone($timezone) : $date;
}
if ($date instanceof \DateTime || $date instanceof \DateTimeInterface) {
$date = clone $date;
if (false !== $timezone) {
$date->setTimezone($timezone);
}
return $date;
}
if (null === $date ||'now'=== $date) {
return new \DateTime($date, false !== $timezone ? $timezone : $env->getExtension('\Twig\Extension\CoreExtension')->getTimezone());
}
$asString = (string) $date;
if (ctype_digit($asString) || (!empty($asString) &&'-'=== $asString[0] && ctype_digit(substr($asString, 1)))) {
$date = new \DateTime('@'.$date);
} else {
$date = new \DateTime($date, $env->getExtension('\Twig\Extension\CoreExtension')->getTimezone());
}
if (false !== $timezone) {
$date->setTimezone($timezone);
}
return $date;
}
function twig_replace_filter($str, $from, $to = null)
{
if (\is_string($from) && \is_string($to)) {
@trigger_error('Using "replace" with character by character replacement is deprecated since version 1.22 and will be removed in Twig 2.0', E_USER_DEPRECATED);
return strtr($str, $from, $to);
}
if (!twig_test_iterable($from)) {
throw new RuntimeError(sprintf('The "replace" filter expects an array or "Traversable" as replace values, got "%s".', \is_object($from) ? \get_class($from) : \gettype($from)));
}
return strtr($str, twig_to_array($from));
}
function twig_round($value, $precision = 0, $method ='common')
{
if ('common'== $method) {
return round($value, $precision);
}
if ('ceil'!= $method &&'floor'!= $method) {
throw new RuntimeError('The round filter only supports the "common", "ceil", and "floor" methods.');
}
return $method($value * pow(10, $precision)) / pow(10, $precision);
}
function twig_number_format_filter(Environment $env, $number, $decimal = null, $decimalPoint = null, $thousandSep = null)
{
$defaults = $env->getExtension('\Twig\Extension\CoreExtension')->getNumberFormat();
if (null === $decimal) {
$decimal = $defaults[0];
}
if (null === $decimalPoint) {
$decimalPoint = $defaults[1];
}
if (null === $thousandSep) {
$thousandSep = $defaults[2];
}
return number_format((float) $number, $decimal, $decimalPoint, $thousandSep);
}
function twig_urlencode_filter($url)
{
if (\is_array($url)) {
if (\defined('PHP_QUERY_RFC3986')) {
return http_build_query($url,'','&', PHP_QUERY_RFC3986);
}
return http_build_query($url,'','&');
}
return rawurlencode($url);
}
function twig_jsonencode_filter($value, $options = 0)
{
if ($value instanceof Markup) {
$value = (string) $value;
} elseif (\is_array($value)) {
array_walk_recursive($value,'_twig_markup2string');
}
return json_encode($value, $options);
}
function _twig_markup2string(&$value)
{
if ($value instanceof Markup) {
$value = (string) $value;
}
}
function twig_array_merge($arr1, $arr2)
{
if (!twig_test_iterable($arr1)) {
throw new RuntimeError(sprintf('The merge filter only works with arrays or "Traversable", got "%s" as first argument.', \gettype($arr1)));
}
if (!twig_test_iterable($arr2)) {
throw new RuntimeError(sprintf('The merge filter only works with arrays or "Traversable", got "%s" as second argument.', \gettype($arr2)));
}
return array_merge(twig_to_array($arr1), twig_to_array($arr2));
}
function twig_slice(Environment $env, $item, $start, $length = null, $preserveKeys = false)
{
if ($item instanceof \Traversable) {
while ($item instanceof \IteratorAggregate) {
$item = $item->getIterator();
}
if ($start >= 0 && $length >= 0 && $item instanceof \Iterator) {
try {
return iterator_to_array(new \LimitIterator($item, $start, null === $length ? -1 : $length), $preserveKeys);
} catch (\OutOfBoundsException $e) {
return [];
}
}
$item = iterator_to_array($item, $preserveKeys);
}
if (\is_array($item)) {
return \array_slice($item, $start, $length, $preserveKeys);
}
$item = (string) $item;
if (\function_exists('mb_get_info') && null !== $charset = $env->getCharset()) {
return (string) mb_substr($item, $start, null === $length ? mb_strlen($item, $charset) - $start : $length, $charset);
}
return (string) (null === $length ? substr($item, $start) : substr($item, $start, $length));
}
function twig_first(Environment $env, $item)
{
$elements = twig_slice($env, $item, 0, 1, false);
return \is_string($elements) ? $elements : current($elements);
}
function twig_last(Environment $env, $item)
{
$elements = twig_slice($env, $item, -1, 1, false);
return \is_string($elements) ? $elements : current($elements);
}
function twig_join_filter($value, $glue ='', $and = null)
{
if (!twig_test_iterable($value)) {
$value = (array) $value;
}
$value = twig_to_array($value, false);
if (0 === \count($value)) {
return'';
}
if (null === $and || $and === $glue) {
return implode($glue, $value);
}
if (1 === \count($value)) {
return $value[0];
}
return implode($glue, \array_slice($value, 0, -1)).$and.$value[\count($value) - 1];
}
function twig_split_filter(Environment $env, $value, $delimiter, $limit = null)
{
if (!empty($delimiter)) {
return null === $limit ? explode($delimiter, $value) : explode($delimiter, $value, $limit);
}
if (!\function_exists('mb_get_info') || null === $charset = $env->getCharset()) {
return str_split($value, null === $limit ? 1 : $limit);
}
if ($limit <= 1) {
return preg_split('/(?<!^)(?!$)/u', $value);
}
$length = mb_strlen($value, $charset);
if ($length < $limit) {
return [$value];
}
$r = [];
for ($i = 0; $i < $length; $i += $limit) {
$r[] = mb_substr($value, $i, $limit, $charset);
}
return $r;
}
function _twig_default_filter($value, $default ='')
{
if (twig_test_empty($value)) {
return $default;
}
return $value;
}
function twig_get_array_keys_filter($array)
{
if ($array instanceof \Traversable) {
while ($array instanceof \IteratorAggregate) {
$array = $array->getIterator();
}
if ($array instanceof \Iterator) {
$keys = [];
$array->rewind();
while ($array->valid()) {
$keys[] = $array->key();
$array->next();
}
return $keys;
}
$keys = [];
foreach ($array as $key => $item) {
$keys[] = $key;
}
return $keys;
}
if (!\is_array($array)) {
return [];
}
return array_keys($array);
}
function twig_reverse_filter(Environment $env, $item, $preserveKeys = false)
{
if ($item instanceof \Traversable) {
return array_reverse(iterator_to_array($item), $preserveKeys);
}
if (\is_array($item)) {
return array_reverse($item, $preserveKeys);
}
if (null !== $charset = $env->getCharset()) {
$string = (string) $item;
if ('UTF-8'!== $charset) {
$item = twig_convert_encoding($string,'UTF-8', $charset);
}
preg_match_all('/./us', $item, $matches);
$string = implode('', array_reverse($matches[0]));
if ('UTF-8'!== $charset) {
$string = twig_convert_encoding($string, $charset,'UTF-8');
}
return $string;
}
return strrev((string) $item);
}
function twig_sort_filter($array)
{
if ($array instanceof \Traversable) {
$array = iterator_to_array($array);
} elseif (!\is_array($array)) {
throw new RuntimeError(sprintf('The sort filter only works with arrays or "Traversable", got "%s".', \gettype($array)));
}
asort($array);
return $array;
}
function twig_in_filter($value, $compare)
{
if ($value instanceof Markup) {
$value = (string) $value;
}
if ($compare instanceof Markup) {
$compare = (string) $compare;
}
if (\is_array($compare)) {
return \in_array($value, $compare, \is_object($value) || \is_resource($value));
} elseif (\is_string($compare) && (\is_string($value) || \is_int($value) || \is_float($value))) {
return''=== $value || false !== strpos($compare, (string) $value);
} elseif ($compare instanceof \Traversable) {
if (\is_object($value) || \is_resource($value)) {
foreach ($compare as $item) {
if ($item === $value) {
return true;
}
}
} else {
foreach ($compare as $item) {
if ($item == $value) {
return true;
}
}
}
return false;
}
return false;
}
function twig_trim_filter($string, $characterMask = null, $side ='both')
{
if (null === $characterMask) {
$characterMask =" \t\n\r\0\x0B";
}
switch ($side) {
case'both':
return trim($string, $characterMask);
case'left':
return ltrim($string, $characterMask);
case'right':
return rtrim($string, $characterMask);
default:
throw new RuntimeError('Trimming side must be "left", "right" or "both".');
}
}
function twig_spaceless($content)
{
return trim(preg_replace('/>\s+</','><', $content));
}
function twig_escape_filter(Environment $env, $string, $strategy ='html', $charset = null, $autoescape = false)
{
if ($autoescape && $string instanceof Markup) {
return $string;
}
if (!\is_string($string)) {
if (\is_object($string) && method_exists($string,'__toString')) {
$string = (string) $string;
} elseif (\in_array($strategy, ['html','js','css','html_attr','url'])) {
return $string;
}
}
if (''=== $string) {
return'';
}
if (null === $charset) {
$charset = $env->getCharset();
}
switch ($strategy) {
case'html':
static $htmlspecialcharsCharsets = ['ISO-8859-1'=> true,'ISO8859-1'=> true,'ISO-8859-15'=> true,'ISO8859-15'=> true,'utf-8'=> true,'UTF-8'=> true,'CP866'=> true,'IBM866'=> true,'866'=> true,'CP1251'=> true,'WINDOWS-1251'=> true,'WIN-1251'=> true,'1251'=> true,'CP1252'=> true,'WINDOWS-1252'=> true,'1252'=> true,'KOI8-R'=> true,'KOI8-RU'=> true,'KOI8R'=> true,'BIG5'=> true,'950'=> true,'GB2312'=> true,'936'=> true,'BIG5-HKSCS'=> true,'SHIFT_JIS'=> true,'SJIS'=> true,'932'=> true,'EUC-JP'=> true,'EUCJP'=> true,'ISO8859-5'=> true,'ISO-8859-5'=> true,'MACROMAN'=> true,
];
if (isset($htmlspecialcharsCharsets[$charset])) {
return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
}
if (isset($htmlspecialcharsCharsets[strtoupper($charset)])) {
$htmlspecialcharsCharsets[$charset] = true;
return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, $charset);
}
$string = twig_convert_encoding($string,'UTF-8', $charset);
$string = htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE,'UTF-8');
return twig_convert_encoding($string, $charset,'UTF-8');
case'js':
if ('UTF-8'!== $charset) {
$string = twig_convert_encoding($string,'UTF-8', $charset);
}
if (!preg_match('//u', $string)) {
throw new RuntimeError('The string to escape is not a valid UTF-8 string.');
}
$string = preg_replace_callback('#[^a-zA-Z0-9,\._]#Su','_twig_escape_js_callback', $string);
if ('UTF-8'!== $charset) {
$string = twig_convert_encoding($string, $charset,'UTF-8');
}
return $string;
case'css':
if ('UTF-8'!== $charset) {
$string = twig_convert_encoding($string,'UTF-8', $charset);
}
if (!preg_match('//u', $string)) {
throw new RuntimeError('The string to escape is not a valid UTF-8 string.');
}
$string = preg_replace_callback('#[^a-zA-Z0-9]#Su','_twig_escape_css_callback', $string);
if ('UTF-8'!== $charset) {
$string = twig_convert_encoding($string, $charset,'UTF-8');
}
return $string;
case'html_attr':
if ('UTF-8'!== $charset) {
$string = twig_convert_encoding($string,'UTF-8', $charset);
}
if (!preg_match('//u', $string)) {
throw new RuntimeError('The string to escape is not a valid UTF-8 string.');
}
$string = preg_replace_callback('#[^a-zA-Z0-9,\.\-_]#Su','_twig_escape_html_attr_callback', $string);
if ('UTF-8'!== $charset) {
$string = twig_convert_encoding($string, $charset,'UTF-8');
}
return $string;
case'url':
return rawurlencode($string);
default:
static $escapers;
if (null === $escapers) {
$escapers = $env->getExtension('\Twig\Extension\CoreExtension')->getEscapers();
}
if (isset($escapers[$strategy])) {
return \call_user_func($escapers[$strategy], $env, $string, $charset);
}
$validStrategies = implode(', ', array_merge(['html','js','url','css','html_attr'], array_keys($escapers)));
throw new RuntimeError(sprintf('Invalid escaping strategy "%s" (valid ones: %s).', $strategy, $validStrategies));
}
}
function twig_escape_filter_is_safe(Node $filterArgs)
{
foreach ($filterArgs as $arg) {
if ($arg instanceof ConstantExpression) {
return [$arg->getAttribute('value')];
}
return [];
}
return ['html'];
}
if (\function_exists('mb_convert_encoding')) {
function twig_convert_encoding($string, $to, $from)
{
return mb_convert_encoding($string, $to, $from);
}
} elseif (\function_exists('iconv')) {
function twig_convert_encoding($string, $to, $from)
{
return iconv($from, $to, $string);
}
} else {
function twig_convert_encoding($string, $to, $from)
{
throw new RuntimeError('No suitable convert encoding function (use UTF-8 as your encoding or install the iconv or mbstring extension).');
}
}
if (\function_exists('mb_ord')) {
function twig_ord($string)
{
return mb_ord($string,'UTF-8');
}
} else {
function twig_ord($string)
{
$code = ($string = unpack('C*', substr($string, 0, 4))) ? $string[1] : 0;
if (0xF0 <= $code) {
return (($code - 0xF0) << 18) + (($string[2] - 0x80) << 12) + (($string[3] - 0x80) << 6) + $string[4] - 0x80;
}
if (0xE0 <= $code) {
return (($code - 0xE0) << 12) + (($string[2] - 0x80) << 6) + $string[3] - 0x80;
}
if (0xC0 <= $code) {
return (($code - 0xC0) << 6) + $string[2] - 0x80;
}
return $code;
}
}
function _twig_escape_js_callback($matches)
{
$char = $matches[0];
static $shortMap = ['\\'=>'\\\\','/'=>'\\/',"\x08"=>'\b',"\x0C"=>'\f',"\x0A"=>'\n',"\x0D"=>'\r',"\x09"=>'\t',
];
if (isset($shortMap[$char])) {
return $shortMap[$char];
}
$char = twig_convert_encoding($char,'UTF-16BE','UTF-8');
$char = strtoupper(bin2hex($char));
if (4 >= \strlen($char)) {
return sprintf('\u%04s', $char);
}
return sprintf('\u%04s\u%04s', substr($char, 0, -4), substr($char, -4));
}
function _twig_escape_css_callback($matches)
{
$char = $matches[0];
return sprintf('\\%X ', 1 === \strlen($char) ? \ord($char) : twig_ord($char));
}
function _twig_escape_html_attr_callback($matches)
{
$chr = $matches[0];
$ord = \ord($chr);
if (($ord <= 0x1f &&"\t"!= $chr &&"\n"!= $chr &&"\r"!= $chr) || ($ord >= 0x7f && $ord <= 0x9f)) {
return'&#xFFFD;';
}
if (1 == \strlen($chr)) {
static $entityMap = [
34 =>'&quot;',
38 =>'&amp;',
60 =>'&lt;',
62 =>'&gt;',
];
if (isset($entityMap[$ord])) {
return $entityMap[$ord];
}
return sprintf('&#x%02X;', $ord);
}
return sprintf('&#x%04X;', twig_ord($chr));
}
if (\function_exists('mb_get_info')) {
function twig_length_filter(Environment $env, $thing)
{
if (null === $thing) {
return 0;
}
if (is_scalar($thing)) {
return mb_strlen($thing, $env->getCharset());
}
if ($thing instanceof \Countable || \is_array($thing) || $thing instanceof \SimpleXMLElement) {
return \count($thing);
}
if ($thing instanceof \Traversable) {
return iterator_count($thing);
}
if (\is_object($thing) && method_exists($thing,'__toString')) {
return mb_strlen((string) $thing, $env->getCharset());
}
return 1;
}
function twig_upper_filter(Environment $env, $string)
{
if (null !== $charset = $env->getCharset()) {
return mb_strtoupper($string, $charset);
}
return strtoupper($string);
}
function twig_lower_filter(Environment $env, $string)
{
if (null !== $charset = $env->getCharset()) {
return mb_strtolower($string, $charset);
}
return strtolower($string);
}
function twig_title_string_filter(Environment $env, $string)
{
if (null !== $charset = $env->getCharset()) {
return mb_convert_case($string, MB_CASE_TITLE, $charset);
}
return ucwords(strtolower($string));
}
function twig_capitalize_string_filter(Environment $env, $string)
{
if (null !== $charset = $env->getCharset()) {
return mb_strtoupper(mb_substr($string, 0, 1, $charset), $charset).mb_strtolower(mb_substr($string, 1, mb_strlen($string, $charset), $charset), $charset);
}
return ucfirst(strtolower($string));
}
}
else {
function twig_length_filter(Environment $env, $thing)
{
if (null === $thing) {
return 0;
}
if (is_scalar($thing)) {
return \strlen($thing);
}
if ($thing instanceof \SimpleXMLElement) {
return \count($thing);
}
if (\is_object($thing) && method_exists($thing,'__toString') && !$thing instanceof \Countable) {
return \strlen((string) $thing);
}
if ($thing instanceof \Countable || \is_array($thing)) {
return \count($thing);
}
if ($thing instanceof \IteratorAggregate) {
return iterator_count($thing);
}
return 1;
}
function twig_title_string_filter(Environment $env, $string)
{
return ucwords(strtolower($string));
}
function twig_capitalize_string_filter(Environment $env, $string)
{
return ucfirst(strtolower($string));
}
}
function twig_ensure_traversable($seq)
{
if ($seq instanceof \Traversable || \is_array($seq)) {
return $seq;
}
return [];
}
function twig_to_array($seq, $preserveKeys = true)
{
if ($seq instanceof \Traversable) {
return iterator_to_array($seq, $preserveKeys);
}
if (!\is_array($seq)) {
return $seq;
}
return $preserveKeys ? $seq : array_values($seq);
}
function twig_test_empty($value)
{
if ($value instanceof \Countable) {
return 0 == \count($value);
}
if (\is_object($value) && method_exists($value,'__toString')) {
return''=== (string) $value;
}
return''=== $value || false === $value || null === $value || [] === $value;
}
function twig_test_iterable($value)
{
return $value instanceof \Traversable || \is_array($value);
}
function twig_include(Environment $env, $context, $template, $variables = [], $withContext = true, $ignoreMissing = false, $sandboxed = false)
{
$alreadySandboxed = false;
$sandbox = null;
if ($withContext) {
$variables = array_merge($context, $variables);
}
if ($isSandboxed = $sandboxed && $env->hasExtension('\Twig\Extension\SandboxExtension')) {
$sandbox = $env->getExtension('\Twig\Extension\SandboxExtension');
if (!$alreadySandboxed = $sandbox->isSandboxed()) {
$sandbox->enableSandbox();
}
}
$loaded = null;
try {
$loaded = $env->resolveTemplate($template);
} catch (LoaderError $e) {
if (!$ignoreMissing) {
if ($isSandboxed && !$alreadySandboxed) {
$sandbox->disableSandbox();
}
throw $e;
}
} catch (\Throwable $e) {
if ($isSandboxed && !$alreadySandboxed) {
$sandbox->disableSandbox();
}
throw $e;
} catch (\Exception $e) {
if ($isSandboxed && !$alreadySandboxed) {
$sandbox->disableSandbox();
}
throw $e;
}
try {
$ret = $loaded ? $loaded->render($variables) :'';
} catch (\Exception $e) {
if ($isSandboxed && !$alreadySandboxed) {
$sandbox->disableSandbox();
}
throw $e;
}
if ($isSandboxed && !$alreadySandboxed) {
$sandbox->disableSandbox();
}
return $ret;
}
function twig_source(Environment $env, $name, $ignoreMissing = false)
{
$loader = $env->getLoader();
try {
if (!$loader instanceof SourceContextLoaderInterface) {
return $loader->getSource($name);
} else {
return $loader->getSourceContext($name)->getCode();
}
} catch (LoaderError $e) {
if (!$ignoreMissing) {
throw $e;
}
}
}
function twig_constant($constant, $object = null)
{
if (null !== $object) {
$constant = \get_class($object).'::'.$constant;
}
return \constant($constant);
}
function twig_constant_is_defined($constant, $object = null)
{
if (null !== $object) {
$constant = \get_class($object).'::'.$constant;
}
return \defined($constant);
}
function twig_array_batch($items, $size, $fill = null, $preserveKeys = true)
{
if (!twig_test_iterable($items)) {
throw new RuntimeError(sprintf('The "batch" filter expects an array or "Traversable", got "%s".', \is_object($items) ? \get_class($items) : \gettype($items)));
}
$size = ceil($size);
$result = array_chunk(twig_to_array($items, $preserveKeys), $size, $preserveKeys);
if (null !== $fill && $result) {
$last = \count($result) - 1;
if ($fillCount = $size - \count($result[$last])) {
for ($i = 0; $i < $fillCount; ++$i) {
$result[$last][] = $fill;
}
}
}
return $result;
}
function twig_array_filter($array, $arrow)
{
if (\is_array($array)) {
if (\PHP_VERSION_ID >= 50600) {
return array_filter($array, $arrow, \ARRAY_FILTER_USE_BOTH);
}
return array_filter($array, $arrow);
}
return new \CallbackFilterIterator(new \IteratorIterator($array), $arrow);
}
function twig_array_map($array, $arrow)
{
$r = [];
foreach ($array as $k => $v) {
$r[$k] = $arrow($v, $k);
}
return $r;
}
function twig_array_reduce($array, $arrow, $initial = null)
{
if (!\is_array($array)) {
$array = iterator_to_array($array);
}
return array_reduce($array, $arrow, $initial);
}
}
namespace Twig\Extension {
use Twig\NodeVisitor\EscaperNodeVisitor;
use Twig\TokenParser\AutoEscapeTokenParser;
use Twig\TwigFilter;
class EscaperExtension extends AbstractExtension
{
protected $defaultStrategy;
public function __construct($defaultStrategy ='html')
{
$this->setDefaultStrategy($defaultStrategy);
}
public function getTokenParsers()
{
return [new AutoEscapeTokenParser()];
}
public function getNodeVisitors()
{
return [new EscaperNodeVisitor()];
}
public function getFilters()
{
return [
new TwigFilter('raw','twig_raw_filter', ['is_safe'=> ['all']]),
];
}
public function setDefaultStrategy($defaultStrategy)
{
if (true === $defaultStrategy) {
@trigger_error('Using "true" as the default strategy is deprecated since version 1.21. Use "html" instead.', E_USER_DEPRECATED);
$defaultStrategy ='html';
}
if ('filename'=== $defaultStrategy) {
@trigger_error('Using "filename" as the default strategy is deprecated since version 1.27. Use "name" instead.', E_USER_DEPRECATED);
$defaultStrategy ='name';
}
if ('name'=== $defaultStrategy) {
$defaultStrategy = ['\Twig\FileExtensionEscapingStrategy','guess'];
}
$this->defaultStrategy = $defaultStrategy;
}
public function getDefaultStrategy($name)
{
if (!\is_string($this->defaultStrategy) && false !== $this->defaultStrategy) {
return \call_user_func($this->defaultStrategy, $name);
}
return $this->defaultStrategy;
}
public function getName()
{
return'escaper';
}
}
class_alias('Twig\Extension\EscaperExtension','Twig_Extension_Escaper');
}
namespace {
function twig_raw_filter($string)
{
return $string;
}
}
namespace Twig\Extension
{
use Twig\NodeVisitor\OptimizerNodeVisitor;
class OptimizerExtension extends AbstractExtension
{
protected $optimizers;
public function __construct($optimizers = -1)
{
$this->optimizers = $optimizers;
}
public function getNodeVisitors()
{
return [new OptimizerNodeVisitor($this->optimizers)];
}
public function getName()
{
return'optimizer';
}
}
class_alias('Twig\Extension\OptimizerExtension','Twig_Extension_Optimizer');
}
namespace Twig\Loader
{
use Twig\Error\LoaderError;
interface LoaderInterface
{
public function getSource($name);
public function getCacheKey($name);
public function isFresh($name, $time);
}
class_alias('Twig\Loader\LoaderInterface','Twig_LoaderInterface');
}
namespace Twig
{
class Markup implements \Countable
{
protected $content;
protected $charset;
public function __construct($content, $charset)
{
$this->content = (string) $content;
$this->charset = $charset;
}
public function __toString()
{
return $this->content;
}
public function count()
{
return \function_exists('mb_get_info') ? mb_strlen($this->content, $this->charset) : \strlen($this->content);
}
}
class_alias('Twig\Markup','Twig_Markup');
}
namespace
{
use Twig\Environment;
interface Twig_TemplateInterface
{
const ANY_CALL ='any';
const ARRAY_CALL ='array';
const METHOD_CALL ='method';
public function render(array $context);
public function display(array $context, array $blocks = []);
public function getEnvironment();
}
}
namespace Twig
{
use Twig\Error\Error;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
abstract class Template implements \Twig_TemplateInterface
{
protected static $cache = [];
protected $parent;
protected $parents = [];
protected $env;
protected $blocks = [];
protected $traits = [];
protected $sandbox;
public function __construct(Environment $env)
{
$this->env = $env;
}
public function __toString()
{
return $this->getTemplateName();
}
abstract public function getTemplateName();
public function getDebugInfo()
{
return [];
}
public function getSource()
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);
return'';
}
public function getSourceContext()
{
return new Source('', $this->getTemplateName());
}
public function getEnvironment()
{
@trigger_error('The '.__METHOD__.' method is deprecated since version 1.20 and will be removed in 2.0.', E_USER_DEPRECATED);
return $this->env;
}
public function getParent(array $context)
{
if (null !== $this->parent) {
return $this->parent;
}
try {
$parent = $this->doGetParent($context);
if (false === $parent) {
return false;
}
if ($parent instanceof self || $parent instanceof TemplateWrapper) {
return $this->parents[$parent->getSourceContext()->getName()] = $parent;
}
if (!isset($this->parents[$parent])) {
$this->parents[$parent] = $this->loadTemplate($parent);
}
} catch (LoaderError $e) {
$e->setSourceContext(null);
$e->guess();
throw $e;
}
return $this->parents[$parent];
}
protected function doGetParent(array $context)
{
return false;
}
public function isTraitable()
{
return true;
}
public function displayParentBlock($name, array $context, array $blocks = [])
{
$name = (string) $name;
if (isset($this->traits[$name])) {
$this->traits[$name][0]->displayBlock($name, $context, $blocks, false);
} elseif (false !== $parent = $this->getParent($context)) {
$parent->displayBlock($name, $context, $blocks, false);
} else {
throw new RuntimeError(sprintf('The template has no parent and no traits defining the "%s" block.', $name), -1, $this->getSourceContext());
}
}
public function displayBlock($name, array $context, array $blocks = [], $useBlocks = true)
{
$name = (string) $name;
if ($useBlocks && isset($blocks[$name])) {
$template = $blocks[$name][0];
$block = $blocks[$name][1];
} elseif (isset($this->blocks[$name])) {
$template = $this->blocks[$name][0];
$block = $this->blocks[$name][1];
} else {
$template = null;
$block = null;
}
if (null !== $template && !$template instanceof self) {
throw new \LogicException('A block must be a method on a \Twig\Template instance.');
}
if (null !== $template) {
try {
$template->$block($context, $blocks);
} catch (Error $e) {
if (!$e->getSourceContext()) {
$e->setSourceContext($template->getSourceContext());
}
if (-1 === $e->getTemplateLine()) {
$e->guess();
}
throw $e;
} catch (\Exception $e) {
$e = new RuntimeError(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, $template->getSourceContext(), $e);
$e->guess();
throw $e;
}
} elseif (false !== $parent = $this->getParent($context)) {
$parent->displayBlock($name, $context, array_merge($this->blocks, $blocks), false);
} else {
@trigger_error(sprintf('Silent display of undefined block "%s" in template "%s" is deprecated since version 1.29 and will throw an exception in 2.0. Use the "block(\'%s\') is defined" expression to test for block existence.', $name, $this->getTemplateName(), $name), E_USER_DEPRECATED);
}
}
public function renderParentBlock($name, array $context, array $blocks = [])
{
ob_start(function () { return''; });
$this->displayParentBlock($name, $context, $blocks);
return ob_get_clean();
}
public function renderBlock($name, array $context, array $blocks = [], $useBlocks = true)
{
ob_start(function () { return''; });
$this->displayBlock($name, $context, $blocks, $useBlocks);
return ob_get_clean();
}
public function hasBlock($name, array $context = null, array $blocks = [])
{
if (null === $context) {
@trigger_error('The '.__METHOD__.' method is internal and should never be called; calling it directly is deprecated since version 1.28 and won\'t be possible anymore in 2.0.', E_USER_DEPRECATED);
return isset($this->blocks[(string) $name]);
}
if (isset($blocks[$name])) {
return $blocks[$name][0] instanceof self;
}
if (isset($this->blocks[$name])) {
return true;
}
if (false !== $parent = $this->getParent($context)) {
return $parent->hasBlock($name, $context);
}
return false;
}
public function getBlockNames(array $context = null, array $blocks = [])
{
if (null === $context) {
@trigger_error('The '.__METHOD__.' method is internal and should never be called; calling it directly is deprecated since version 1.28 and won\'t be possible anymore in 2.0.', E_USER_DEPRECATED);
return array_keys($this->blocks);
}
$names = array_merge(array_keys($blocks), array_keys($this->blocks));
if (false !== $parent = $this->getParent($context)) {
$names = array_merge($names, $parent->getBlockNames($context));
}
return array_unique($names);
}
protected function loadTemplate($template, $templateName = null, $line = null, $index = null)
{
try {
if (\is_array($template)) {
return $this->env->resolveTemplate($template);
}
if ($template instanceof self || $template instanceof TemplateWrapper) {
return $template;
}
if ($template === $this->getTemplateName()) {
$class = \get_class($this);
if (false !== $pos = strrpos($class,'___', -1)) {
$class = substr($class, 0, $pos);
}
return $this->env->loadClass($class, $template, $index);
}
return $this->env->loadTemplate($template, $index);
} catch (Error $e) {
if (!$e->getSourceContext()) {
$e->setSourceContext($templateName ? new Source('', $templateName) : $this->getSourceContext());
}
if ($e->getTemplateLine() > 0) {
throw $e;
}
if (!$line) {
$e->guess();
} else {
$e->setTemplateLine($line);
}
throw $e;
}
}
protected function unwrap()
{
return $this;
}
public function getBlocks()
{
return $this->blocks;
}
public function display(array $context, array $blocks = [])
{
$this->displayWithErrorHandling($this->env->mergeGlobals($context), array_merge($this->blocks, $blocks));
}
public function render(array $context)
{
$level = ob_get_level();
ob_start(function () { return''; });
try {
$this->display($context);
} catch (\Exception $e) {
while (ob_get_level() > $level) {
ob_end_clean();
}
throw $e;
} catch (\Throwable $e) {
while (ob_get_level() > $level) {
ob_end_clean();
}
throw $e;
}
return ob_get_clean();
}
protected function displayWithErrorHandling(array $context, array $blocks = [])
{
try {
$this->doDisplay($context, $blocks);
} catch (Error $e) {
if (!$e->getSourceContext()) {
$e->setSourceContext($this->getSourceContext());
}
if (-1 === $e->getTemplateLine()) {
$e->guess();
}
throw $e;
} catch (\Exception $e) {
$e = new RuntimeError(sprintf('An exception has been thrown during the rendering of a template ("%s").', $e->getMessage()), -1, $this->getSourceContext(), $e);
$e->guess();
throw $e;
}
}
abstract protected function doDisplay(array $context, array $blocks = []);
final protected function getContext($context, $item, $ignoreStrictCheck = false)
{
if (!\array_key_exists($item, $context)) {
if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
return;
}
throw new RuntimeError(sprintf('Variable "%s" does not exist.', $item), -1, $this->getSourceContext());
}
return $context[$item];
}
protected function getAttribute($object, $item, array $arguments = [], $type = self::ANY_CALL, $isDefinedTest = false, $ignoreStrictCheck = false)
{
if (self::METHOD_CALL !== $type) {
$arrayItem = \is_bool($item) || \is_float($item) ? (int) $item : $item;
if (((\is_array($object) || $object instanceof \ArrayObject) && (isset($object[$arrayItem]) || \array_key_exists($arrayItem, $object)))
|| ($object instanceof \ArrayAccess && isset($object[$arrayItem]))
) {
if ($isDefinedTest) {
return true;
}
return $object[$arrayItem];
}
if (self::ARRAY_CALL === $type || !\is_object($object)) {
if ($isDefinedTest) {
return false;
}
if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
return;
}
if ($object instanceof \ArrayAccess) {
$message = sprintf('Key "%s" in object with ArrayAccess of class "%s" does not exist.', $arrayItem, \get_class($object));
} elseif (\is_object($object)) {
$message = sprintf('Impossible to access a key "%s" on an object of class "%s" that does not implement ArrayAccess interface.', $item, \get_class($object));
} elseif (\is_array($object)) {
if (empty($object)) {
$message = sprintf('Key "%s" does not exist as the array is empty.', $arrayItem);
} else {
$message = sprintf('Key "%s" for array with keys "%s" does not exist.', $arrayItem, implode(', ', array_keys($object)));
}
} elseif (self::ARRAY_CALL === $type) {
if (null === $object) {
$message = sprintf('Impossible to access a key ("%s") on a null variable.', $item);
} else {
$message = sprintf('Impossible to access a key ("%s") on a %s variable ("%s").', $item, \gettype($object), $object);
}
} elseif (null === $object) {
$message = sprintf('Impossible to access an attribute ("%s") on a null variable.', $item);
} else {
$message = sprintf('Impossible to access an attribute ("%s") on a %s variable ("%s").', $item, \gettype($object), $object);
}
throw new RuntimeError($message, -1, $this->getSourceContext());
}
}
if (!\is_object($object)) {
if ($isDefinedTest) {
return false;
}
if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
return;
}
if (null === $object) {
$message = sprintf('Impossible to invoke a method ("%s") on a null variable.', $item);
} elseif (\is_array($object)) {
$message = sprintf('Impossible to invoke a method ("%s") on an array.', $item);
} else {
$message = sprintf('Impossible to invoke a method ("%s") on a %s variable ("%s").', $item, \gettype($object), $object);
}
throw new RuntimeError($message, -1, $this->getSourceContext());
}
if (self::METHOD_CALL !== $type && !$object instanceof self) { if (isset($object->$item) || \array_key_exists((string) $item, $object)) {
if ($isDefinedTest) {
return true;
}
if ($this->env->hasExtension('\Twig\Extension\SandboxExtension')) {
$this->env->getExtension('\Twig\Extension\SandboxExtension')->checkPropertyAllowed($object, $item);
}
return $object->$item;
}
}
$class = \get_class($object);
if (!isset(self::$cache[$class])) {
if ($object instanceof self) {
$ref = new \ReflectionClass($class);
$methods = [];
foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $refMethod) {
if ('getenvironment'!== strtolower($refMethod->name)) {
$methods[] = $refMethod->name;
}
}
} else {
$methods = get_class_methods($object);
}
sort($methods);
$cache = [];
foreach ($methods as $method) {
$cache[$method] = $method;
$cache[$lcName = strtolower($method)] = $method;
if ('g'=== $lcName[0] && 0 === strpos($lcName,'get')) {
$name = substr($method, 3);
$lcName = substr($lcName, 3);
} elseif ('i'=== $lcName[0] && 0 === strpos($lcName,'is')) {
$name = substr($method, 2);
$lcName = substr($lcName, 2);
} else {
continue;
}
if ($name) {
if (!isset($cache[$name])) {
$cache[$name] = $method;
}
if (!isset($cache[$lcName])) {
$cache[$lcName] = $method;
}
}
}
self::$cache[$class] = $cache;
}
$call = false;
if (isset(self::$cache[$class][$item])) {
$method = self::$cache[$class][$item];
} elseif (isset(self::$cache[$class][$lcItem = strtolower($item)])) {
$method = self::$cache[$class][$lcItem];
} elseif (isset(self::$cache[$class]['__call'])) {
$method = $item;
$call = true;
} else {
if ($isDefinedTest) {
return false;
}
if ($ignoreStrictCheck || !$this->env->isStrictVariables()) {
return;
}
throw new RuntimeError(sprintf('Neither the property "%1$s" nor one of the methods "%1$s()", "get%1$s()"/"is%1$s()" or "__call()" exist and have public access in class "%2$s".', $item, $class), -1, $this->getSourceContext());
}
if ($isDefinedTest) {
return true;
}
if ($this->env->hasExtension('\Twig\Extension\SandboxExtension')) {
$this->env->getExtension('\Twig\Extension\SandboxExtension')->checkMethodAllowed($object, $method);
}
try {
if (!$arguments) {
$ret = $object->$method();
} else {
$ret = \call_user_func_array([$object, $method], $arguments);
}
} catch (\BadMethodCallException $e) {
if ($call && ($ignoreStrictCheck || !$this->env->isStrictVariables())) {
return;
}
throw $e;
}
if ($object instanceof \Twig_TemplateInterface) {
$self = $object->getTemplateName() === $this->getTemplateName();
$message = sprintf('Calling "%s" on template "%s" from template "%s" is deprecated since version 1.28 and won\'t be supported anymore in 2.0.', $item, $object->getTemplateName(), $this->getTemplateName());
if ('renderBlock'=== $method ||'displayBlock'=== $method) {
$message .= sprintf(' Use block("%s"%s) instead).', $arguments[0], $self ?'':', template');
} elseif ('hasBlock'=== $method) {
$message .= sprintf(' Use "block("%s"%s) is defined" instead).', $arguments[0], $self ?'':', template');
} elseif ('render'=== $method ||'display'=== $method) {
$message .= sprintf(' Use include("%s") instead).', $object->getTemplateName());
}
@trigger_error($message, E_USER_DEPRECATED);
return''=== $ret ?'': new Markup($ret, $this->env->getCharset());
}
return $ret;
}
}
class_alias('Twig\Template','Twig_Template');
}
namespace Monolog\Formatter
{
interface FormatterInterface
{
public function format(array $record);
public function formatBatch(array $records);
}
}
namespace Monolog\Formatter
{
use Exception;
use Monolog\Utils;
class NormalizerFormatter implements FormatterInterface
{
const SIMPLE_DATE ="Y-m-d H:i:s";
protected $dateFormat;
public function __construct($dateFormat = null)
{
$this->dateFormat = $dateFormat ?: static::SIMPLE_DATE;
if (!function_exists('json_encode')) {
throw new \RuntimeException('PHP\'s json extension is required to use Monolog\'s NormalizerFormatter');
}
}
public function format(array $record)
{
return $this->normalize($record);
}
public function formatBatch(array $records)
{
foreach ($records as $key => $record) {
$records[$key] = $this->format($record);
}
return $records;
}
protected function normalize($data, $depth = 0)
{
if ($depth > 9) {
return'Over 9 levels deep, aborting normalization';
}
if (null === $data || is_scalar($data)) {
if (is_float($data)) {
if (is_infinite($data)) {
return ($data > 0 ?'':'-') .'INF';
}
if (is_nan($data)) {
return'NaN';
}
}
return $data;
}
if (is_array($data)) {
$normalized = array();
$count = 1;
foreach ($data as $key => $value) {
if ($count++ > 1000) {
$normalized['...'] ='Over 1000 items ('.count($data).' total), aborting normalization';
break;
}
$normalized[$key] = $this->normalize($value, $depth+1);
}
return $normalized;
}
if ($data instanceof \DateTime) {
return $data->format($this->dateFormat);
}
if (is_object($data)) {
if ($data instanceof Exception || (PHP_VERSION_ID > 70000 && $data instanceof \Throwable)) {
return $this->normalizeException($data);
}
if (method_exists($data,'__toString') && !$data instanceof \JsonSerializable) {
$value = $data->__toString();
} else {
$value = $this->toJson($data, true);
}
return sprintf("[object] (%s: %s)", Utils::getClass($data), $value);
}
if (is_resource($data)) {
return sprintf('[resource] (%s)', get_resource_type($data));
}
return'[unknown('.gettype($data).')]';
}
protected function normalizeException($e)
{
if (!$e instanceof Exception && !$e instanceof \Throwable) {
throw new \InvalidArgumentException('Exception/Throwable expected, got '.gettype($e).' / '.Utils::getClass($e));
}
$data = array('class'=> Utils::getClass($e),'message'=> $e->getMessage(),'code'=> $e->getCode(),'file'=> $e->getFile().':'.$e->getLine(),
);
if ($e instanceof \SoapFault) {
if (isset($e->faultcode)) {
$data['faultcode'] = $e->faultcode;
}
if (isset($e->faultactor)) {
$data['faultactor'] = $e->faultactor;
}
if (isset($e->detail)) {
$data['detail'] = $e->detail;
}
}
$trace = $e->getTrace();
foreach ($trace as $frame) {
if (isset($frame['file'])) {
$data['trace'][] = $frame['file'].':'.$frame['line'];
} elseif (isset($frame['function']) && $frame['function'] ==='{closure}') {
$data['trace'][] = $frame['function'];
} else {
if (isset($frame['args'])) {
$frame['args'] = array_map(function ($arg) {
if (is_object($arg) && !($arg instanceof \DateTime || $arg instanceof \DateTimeInterface)) {
return sprintf("[object] (%s)", Utils::getClass($arg));
}
return $arg;
}, $frame['args']);
}
$data['trace'][] = $this->toJson($this->normalize($frame), true);
}
}
if ($previous = $e->getPrevious()) {
$data['previous'] = $this->normalizeException($previous);
}
return $data;
}
protected function toJson($data, $ignoreErrors = false)
{
if ($ignoreErrors) {
return @$this->jsonEncode($data);
}
$json = $this->jsonEncode($data);
if ($json === false) {
$json = $this->handleJsonError(json_last_error(), $data);
}
return $json;
}
private function jsonEncode($data)
{
if (version_compare(PHP_VERSION,'5.4.0','>=')) {
return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
return json_encode($data);
}
private function handleJsonError($code, $data)
{
if ($code !== JSON_ERROR_UTF8) {
$this->throwEncodeError($code, $data);
}
if (is_string($data)) {
$this->detectAndCleanUtf8($data);
} elseif (is_array($data)) {
array_walk_recursive($data, array($this,'detectAndCleanUtf8'));
} else {
$this->throwEncodeError($code, $data);
}
$json = $this->jsonEncode($data);
if ($json === false) {
$this->throwEncodeError(json_last_error(), $data);
}
return $json;
}
private function throwEncodeError($code, $data)
{
switch ($code) {
case JSON_ERROR_DEPTH:
$msg ='Maximum stack depth exceeded';
break;
case JSON_ERROR_STATE_MISMATCH:
$msg ='Underflow or the modes mismatch';
break;
case JSON_ERROR_CTRL_CHAR:
$msg ='Unexpected control character found';
break;
case JSON_ERROR_UTF8:
$msg ='Malformed UTF-8 characters, possibly incorrectly encoded';
break;
default:
$msg ='Unknown error';
}
throw new \RuntimeException('JSON encoding failed: '.$msg.'. Encoding: '.var_export($data, true));
}
public function detectAndCleanUtf8(&$data)
{
if (is_string($data) && !preg_match('//u', $data)) {
$data = preg_replace_callback('/[\x80-\xFF]+/',
function ($m) { return utf8_encode($m[0]); },
$data
);
$data = str_replace(
array('¤','¦','¨','´','¸','¼','½','¾'),
array('€','Š','š','Ž','ž','Œ','œ','Ÿ'),
$data
);
}
}
}
}
namespace Monolog\Formatter
{
use Monolog\Utils;
class LineFormatter extends NormalizerFormatter
{
const SIMPLE_FORMAT ="[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
protected $format;
protected $allowInlineLineBreaks;
protected $ignoreEmptyContextAndExtra;
protected $includeStacktraces;
public function __construct($format = null, $dateFormat = null, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
{
$this->format = $format ?: static::SIMPLE_FORMAT;
$this->allowInlineLineBreaks = $allowInlineLineBreaks;
$this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra;
parent::__construct($dateFormat);
}
public function includeStacktraces($include = true)
{
$this->includeStacktraces = $include;
if ($this->includeStacktraces) {
$this->allowInlineLineBreaks = true;
}
}
public function allowInlineLineBreaks($allow = true)
{
$this->allowInlineLineBreaks = $allow;
}
public function ignoreEmptyContextAndExtra($ignore = true)
{
$this->ignoreEmptyContextAndExtra = $ignore;
}
public function format(array $record)
{
$vars = parent::format($record);
$output = $this->format;
foreach ($vars['extra'] as $var => $val) {
if (false !== strpos($output,'%extra.'.$var.'%')) {
$output = str_replace('%extra.'.$var.'%', $this->stringify($val), $output);
unset($vars['extra'][$var]);
}
}
foreach ($vars['context'] as $var => $val) {
if (false !== strpos($output,'%context.'.$var.'%')) {
$output = str_replace('%context.'.$var.'%', $this->stringify($val), $output);
unset($vars['context'][$var]);
}
}
if ($this->ignoreEmptyContextAndExtra) {
if (empty($vars['context'])) {
unset($vars['context']);
$output = str_replace('%context%','', $output);
}
if (empty($vars['extra'])) {
unset($vars['extra']);
$output = str_replace('%extra%','', $output);
}
}
foreach ($vars as $var => $val) {
if (false !== strpos($output,'%'.$var.'%')) {
$output = str_replace('%'.$var.'%', $this->stringify($val), $output);
}
}
if (false !== strpos($output,'%')) {
$output = preg_replace('/%(?:extra|context)\..+?%/','', $output);
}
return $output;
}
public function formatBatch(array $records)
{
$message ='';
foreach ($records as $record) {
$message .= $this->format($record);
}
return $message;
}
public function stringify($value)
{
return $this->replaceNewlines($this->convertToString($value));
}
protected function normalizeException($e)
{
if (!$e instanceof \Exception && !$e instanceof \Throwable) {
throw new \InvalidArgumentException('Exception/Throwable expected, got '.gettype($e).' / '.Utils::getClass($e));
}
$previousText ='';
if ($previous = $e->getPrevious()) {
do {
$previousText .=', '.Utils::getClass($previous).'(code: '.$previous->getCode().'): '.$previous->getMessage().' at '.$previous->getFile().':'.$previous->getLine();
} while ($previous = $previous->getPrevious());
}
$str ='[object] ('.Utils::getClass($e).'(code: '.$e->getCode().'): '.$e->getMessage().' at '.$e->getFile().':'.$e->getLine().$previousText.')';
if ($this->includeStacktraces) {
$str .="\n[stacktrace]\n".$e->getTraceAsString()."\n";
}
return $str;
}
protected function convertToString($data)
{
if (null === $data || is_bool($data)) {
return var_export($data, true);
}
if (is_scalar($data)) {
return (string) $data;
}
if (version_compare(PHP_VERSION,'5.4.0','>=')) {
return $this->toJson($data, true);
}
return str_replace('\\/','/', @json_encode($data));
}
protected function replaceNewlines($str)
{
if ($this->allowInlineLineBreaks) {
if (0 === strpos($str,'{')) {
return str_replace(array('\r','\n'), array("\r","\n"), $str);
}
return $str;
}
return str_replace(array("\r\n","\r","\n"),' ', $str);
}
}
}
namespace Monolog\Handler
{
use Monolog\Formatter\FormatterInterface;
interface HandlerInterface
{
public function isHandling(array $record);
public function handle(array $record);
public function handleBatch(array $records);
public function pushProcessor($callback);
public function popProcessor();
public function setFormatter(FormatterInterface $formatter);
public function getFormatter();
}
}
namespace Monolog
{
interface ResettableInterface
{
public function reset();
}
}
namespace Monolog\Handler
{
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\ResettableInterface;
abstract class AbstractHandler implements HandlerInterface, ResettableInterface
{
protected $level = Logger::DEBUG;
protected $bubble = true;
protected $formatter;
protected $processors = array();
public function __construct($level = Logger::DEBUG, $bubble = true)
{
$this->setLevel($level);
$this->bubble = $bubble;
}
public function isHandling(array $record)
{
return $record['level'] >= $this->level;
}
public function handleBatch(array $records)
{
foreach ($records as $record) {
$this->handle($record);
}
}
public function close()
{
}
public function pushProcessor($callback)
{
if (!is_callable($callback)) {
throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
}
array_unshift($this->processors, $callback);
return $this;
}
public function popProcessor()
{
if (!$this->processors) {
throw new \LogicException('You tried to pop from an empty processor stack.');
}
return array_shift($this->processors);
}
public function setFormatter(FormatterInterface $formatter)
{
$this->formatter = $formatter;
return $this;
}
public function getFormatter()
{
if (!$this->formatter) {
$this->formatter = $this->getDefaultFormatter();
}
return $this->formatter;
}
public function setLevel($level)
{
$this->level = Logger::toMonologLevel($level);
return $this;
}
public function getLevel()
{
return $this->level;
}
public function setBubble($bubble)
{
$this->bubble = $bubble;
return $this;
}
public function getBubble()
{
return $this->bubble;
}
public function __destruct()
{
try {
$this->close();
} catch (\Exception $e) {
} catch (\Throwable $e) {
}
}
public function reset()
{
foreach ($this->processors as $processor) {
if ($processor instanceof ResettableInterface) {
$processor->reset();
}
}
}
protected function getDefaultFormatter()
{
return new LineFormatter();
}
}
}
namespace Monolog\Handler
{
use Monolog\ResettableInterface;
abstract class AbstractProcessingHandler extends AbstractHandler
{
public function handle(array $record)
{
if (!$this->isHandling($record)) {
return false;
}
$record = $this->processRecord($record);
$record['formatted'] = $this->getFormatter()->format($record);
$this->write($record);
return false === $this->bubble;
}
abstract protected function write(array $record);
protected function processRecord(array $record)
{
if ($this->processors) {
foreach ($this->processors as $processor) {
$record = call_user_func($processor, $record);
}
}
return $record;
}
}
}
namespace Monolog\Handler
{
use Monolog\Logger;
class StreamHandler extends AbstractProcessingHandler
{
protected $stream;
protected $url;
private $errorMessage;
protected $filePermission;
protected $useLocking;
private $dirCreated;
public function __construct($stream, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
{
parent::__construct($level, $bubble);
if (is_resource($stream)) {
$this->stream = $stream;
} elseif (is_string($stream)) {
$this->url = $stream;
} else {
throw new \InvalidArgumentException('A stream must either be a resource or a string.');
}
$this->filePermission = $filePermission;
$this->useLocking = $useLocking;
}
public function close()
{
if ($this->url && is_resource($this->stream)) {
fclose($this->stream);
}
$this->stream = null;
}
public function getStream()
{
return $this->stream;
}
public function getUrl()
{
return $this->url;
}
protected function write(array $record)
{
if (!is_resource($this->stream)) {
if (null === $this->url ||''=== $this->url) {
throw new \LogicException('Missing stream url, the stream can not be opened. This may be caused by a premature call to close().');
}
$this->createDir();
$this->errorMessage = null;
set_error_handler(array($this,'customErrorHandler'));
$this->stream = fopen($this->url,'a');
if ($this->filePermission !== null) {
@chmod($this->url, $this->filePermission);
}
restore_error_handler();
if (!is_resource($this->stream)) {
$this->stream = null;
throw new \UnexpectedValueException(sprintf('The stream or file "%s" could not be opened: '.$this->errorMessage, $this->url));
}
}
if ($this->useLocking) {
flock($this->stream, LOCK_EX);
}
$this->streamWrite($this->stream, $record);
if ($this->useLocking) {
flock($this->stream, LOCK_UN);
}
}
protected function streamWrite($stream, array $record)
{
fwrite($stream, (string) $record['formatted']);
}
private function customErrorHandler($code, $msg)
{
$this->errorMessage = preg_replace('{^(fopen|mkdir)\(.*?\): }','', $msg);
}
private function getDirFromStream($stream)
{
$pos = strpos($stream,'://');
if ($pos === false) {
return dirname($stream);
}
if ('file://'=== substr($stream, 0, 7)) {
return dirname(substr($stream, 7));
}
return;
}
private function createDir()
{
if ($this->dirCreated) {
return;
}
$dir = $this->getDirFromStream($this->url);
if (null !== $dir && !is_dir($dir)) {
$this->errorMessage = null;
set_error_handler(array($this,'customErrorHandler'));
$status = mkdir($dir, 0777, true);
restore_error_handler();
if (false === $status && !is_dir($dir)) {
throw new \UnexpectedValueException(sprintf('There is no existing directory at "%s" and its not buildable: '.$this->errorMessage, $dir));
}
}
$this->dirCreated = true;
}
}
}
namespace Monolog\Handler
{
use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossed\ActivationStrategyInterface;
use Monolog\Logger;
use Monolog\ResettableInterface;
class FingersCrossedHandler extends AbstractHandler
{
protected $handler;
protected $activationStrategy;
protected $buffering = true;
protected $bufferSize;
protected $buffer = array();
protected $stopBuffering;
protected $passthruLevel;
public function __construct($handler, $activationStrategy = null, $bufferSize = 0, $bubble = true, $stopBuffering = true, $passthruLevel = null)
{
if (null === $activationStrategy) {
$activationStrategy = new ErrorLevelActivationStrategy(Logger::WARNING);
}
if (!$activationStrategy instanceof ActivationStrategyInterface) {
$activationStrategy = new ErrorLevelActivationStrategy($activationStrategy);
}
$this->handler = $handler;
$this->activationStrategy = $activationStrategy;
$this->bufferSize = $bufferSize;
$this->bubble = $bubble;
$this->stopBuffering = $stopBuffering;
if ($passthruLevel !== null) {
$this->passthruLevel = Logger::toMonologLevel($passthruLevel);
}
if (!$this->handler instanceof HandlerInterface && !is_callable($this->handler)) {
throw new \RuntimeException("The given handler (".json_encode($this->handler).") is not a callable nor a Monolog\Handler\HandlerInterface object");
}
}
public function isHandling(array $record)
{
return true;
}
public function activate()
{
if ($this->stopBuffering) {
$this->buffering = false;
}
if (!$this->handler instanceof HandlerInterface) {
$record = end($this->buffer) ?: null;
$this->handler = call_user_func($this->handler, $record, $this);
if (!$this->handler instanceof HandlerInterface) {
throw new \RuntimeException("The factory callable should return a HandlerInterface");
}
}
$this->handler->handleBatch($this->buffer);
$this->buffer = array();
}
public function handle(array $record)
{
if ($this->processors) {
foreach ($this->processors as $processor) {
$record = call_user_func($processor, $record);
}
}
if ($this->buffering) {
$this->buffer[] = $record;
if ($this->bufferSize > 0 && count($this->buffer) > $this->bufferSize) {
array_shift($this->buffer);
}
if ($this->activationStrategy->isHandlerActivated($record)) {
$this->activate();
}
} else {
$this->handler->handle($record);
}
return false === $this->bubble;
}
public function close()
{
$this->flushBuffer();
}
public function reset()
{
$this->flushBuffer();
parent::reset();
if ($this->handler instanceof ResettableInterface) {
$this->handler->reset();
}
}
public function clear()
{
$this->buffer = array();
$this->reset();
}
private function flushBuffer()
{
if (null !== $this->passthruLevel) {
$level = $this->passthruLevel;
$this->buffer = array_filter($this->buffer, function ($record) use ($level) {
return $record['level'] >= $level;
});
if (count($this->buffer) > 0) {
$this->handler->handleBatch($this->buffer);
}
}
$this->buffer = array();
$this->buffering = true;
}
}
}
namespace Monolog\Handler
{
use Monolog\Logger;
class FilterHandler extends AbstractHandler
{
protected $handler;
protected $acceptedLevels;
protected $bubble;
public function __construct($handler, $minLevelOrList = Logger::DEBUG, $maxLevel = Logger::EMERGENCY, $bubble = true)
{
$this->handler = $handler;
$this->bubble = $bubble;
$this->setAcceptedLevels($minLevelOrList, $maxLevel);
if (!$this->handler instanceof HandlerInterface && !is_callable($this->handler)) {
throw new \RuntimeException("The given handler (".json_encode($this->handler).") is not a callable nor a Monolog\Handler\HandlerInterface object");
}
}
public function getAcceptedLevels()
{
return array_flip($this->acceptedLevels);
}
public function setAcceptedLevels($minLevelOrList = Logger::DEBUG, $maxLevel = Logger::EMERGENCY)
{
if (is_array($minLevelOrList)) {
$acceptedLevels = array_map('Monolog\Logger::toMonologLevel', $minLevelOrList);
} else {
$minLevelOrList = Logger::toMonologLevel($minLevelOrList);
$maxLevel = Logger::toMonologLevel($maxLevel);
$acceptedLevels = array_values(array_filter(Logger::getLevels(), function ($level) use ($minLevelOrList, $maxLevel) {
return $level >= $minLevelOrList && $level <= $maxLevel;
}));
}
$this->acceptedLevels = array_flip($acceptedLevels);
}
public function isHandling(array $record)
{
return isset($this->acceptedLevels[$record['level']]);
}
public function handle(array $record)
{
if (!$this->isHandling($record)) {
return false;
}
if (!$this->handler instanceof HandlerInterface) {
$this->handler = call_user_func($this->handler, $record, $this);
if (!$this->handler instanceof HandlerInterface) {
throw new \RuntimeException("The factory callable should return a HandlerInterface");
}
}
if ($this->processors) {
foreach ($this->processors as $processor) {
$record = call_user_func($processor, $record);
}
}
$this->handler->handle($record);
return false === $this->bubble;
}
public function handleBatch(array $records)
{
$filtered = array();
foreach ($records as $record) {
if ($this->isHandling($record)) {
$filtered[] = $record;
}
}
$this->handler->handleBatch($filtered);
}
}
}
namespace Monolog\Handler
{
class TestHandler extends AbstractProcessingHandler
{
protected $records = array();
protected $recordsByLevel = array();
public function getRecords()
{
return $this->records;
}
public function clear()
{
$this->records = array();
$this->recordsByLevel = array();
}
public function hasRecords($level)
{
return isset($this->recordsByLevel[$level]);
}
public function hasRecord($record, $level)
{
if (is_string($record)) {
$record = array('message'=> $record);
}
return $this->hasRecordThatPasses(function ($rec) use ($record) {
if ($rec['message'] !== $record['message']) {
return false;
}
if (isset($record['context']) && $rec['context'] !== $record['context']) {
return false;
}
return true;
}, $level);
}
public function hasRecordThatContains($message, $level)
{
return $this->hasRecordThatPasses(function ($rec) use ($message) {
return strpos($rec['message'], $message) !== false;
}, $level);
}
public function hasRecordThatMatches($regex, $level)
{
return $this->hasRecordThatPasses(function ($rec) use ($regex) {
return preg_match($regex, $rec['message']) > 0;
}, $level);
}
public function hasRecordThatPasses($predicate, $level)
{
if (!is_callable($predicate)) {
throw new \InvalidArgumentException("Expected a callable for hasRecordThatSucceeds");
}
if (!isset($this->recordsByLevel[$level])) {
return false;
}
foreach ($this->recordsByLevel[$level] as $i => $rec) {
if (call_user_func($predicate, $rec, $i)) {
return true;
}
}
return false;
}
protected function write(array $record)
{
$this->recordsByLevel[$record['level']][] = $record;
$this->records[] = $record;
}
public function __call($method, $args)
{
if (preg_match('/(.*)(Debug|Info|Notice|Warning|Error|Critical|Alert|Emergency)(.*)/', $method, $matches) > 0) {
$genericMethod = $matches[1] . ('Records'!== $matches[3] ?'Record':'') . $matches[3];
$level = constant('Monolog\Logger::'. strtoupper($matches[2]));
if (method_exists($this, $genericMethod)) {
$args[] = $level;
return call_user_func_array(array($this, $genericMethod), $args);
}
}
throw new \BadMethodCallException('Call to undefined method '. get_class($this) .'::'. $method .'()');
}
}
}
namespace Psr\Log
{
interface LoggerInterface
{
public function emergency($message, array $context = array());
public function alert($message, array $context = array());
public function critical($message, array $context = array());
public function error($message, array $context = array());
public function warning($message, array $context = array());
public function notice($message, array $context = array());
public function info($message, array $context = array());
public function debug($message, array $context = array());
public function log($level, $message, array $context = array());
}
}
namespace Monolog
{
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Psr\Log\InvalidArgumentException;
use Exception;
class Logger implements LoggerInterface, ResettableInterface
{
const DEBUG = 100;
const INFO = 200;
const NOTICE = 250;
const WARNING = 300;
const ERROR = 400;
const CRITICAL = 500;
const ALERT = 550;
const EMERGENCY = 600;
const API = 1;
protected static $levels = array(
self::DEBUG =>'DEBUG',
self::INFO =>'INFO',
self::NOTICE =>'NOTICE',
self::WARNING =>'WARNING',
self::ERROR =>'ERROR',
self::CRITICAL =>'CRITICAL',
self::ALERT =>'ALERT',
self::EMERGENCY =>'EMERGENCY',
);
protected static $timezone;
protected $name;
protected $handlers;
protected $processors;
protected $microsecondTimestamps = true;
protected $exceptionHandler;
public function __construct($name, array $handlers = array(), array $processors = array())
{
$this->name = $name;
$this->setHandlers($handlers);
$this->processors = $processors;
}
public function getName()
{
return $this->name;
}
public function withName($name)
{
$new = clone $this;
$new->name = $name;
return $new;
}
public function pushHandler(HandlerInterface $handler)
{
array_unshift($this->handlers, $handler);
return $this;
}
public function popHandler()
{
if (!$this->handlers) {
throw new \LogicException('You tried to pop from an empty handler stack.');
}
return array_shift($this->handlers);
}
public function setHandlers(array $handlers)
{
$this->handlers = array();
foreach (array_reverse($handlers) as $handler) {
$this->pushHandler($handler);
}
return $this;
}
public function getHandlers()
{
return $this->handlers;
}
public function pushProcessor($callback)
{
if (!is_callable($callback)) {
throw new \InvalidArgumentException('Processors must be valid callables (callback or object with an __invoke method), '.var_export($callback, true).' given');
}
array_unshift($this->processors, $callback);
return $this;
}
public function popProcessor()
{
if (!$this->processors) {
throw new \LogicException('You tried to pop from an empty processor stack.');
}
return array_shift($this->processors);
}
public function getProcessors()
{
return $this->processors;
}
public function useMicrosecondTimestamps($micro)
{
$this->microsecondTimestamps = (bool) $micro;
}
public function addRecord($level, $message, array $context = array())
{
if (!$this->handlers) {
$this->pushHandler(new StreamHandler('php://stderr', static::DEBUG));
}
$levelName = static::getLevelName($level);
$handlerKey = null;
reset($this->handlers);
while ($handler = current($this->handlers)) {
if ($handler->isHandling(array('level'=> $level))) {
$handlerKey = key($this->handlers);
break;
}
next($this->handlers);
}
if (null === $handlerKey) {
return false;
}
if (!static::$timezone) {
static::$timezone = new \DateTimeZone(date_default_timezone_get() ?:'UTC');
}
if ($this->microsecondTimestamps && PHP_VERSION_ID < 70100) {
$ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
} else {
$ts = new \DateTime(null, static::$timezone);
}
$ts->setTimezone(static::$timezone);
$record = array('message'=> (string) $message,'context'=> $context,'level'=> $level,'level_name'=> $levelName,'channel'=> $this->name,'datetime'=> $ts,'extra'=> array(),
);
try {
foreach ($this->processors as $processor) {
$record = call_user_func($processor, $record);
}
while ($handler = current($this->handlers)) {
if (true === $handler->handle($record)) {
break;
}
next($this->handlers);
}
} catch (Exception $e) {
$this->handleException($e, $record);
}
return true;
}
public function close()
{
foreach ($this->handlers as $handler) {
if (method_exists($handler,'close')) {
$handler->close();
}
}
}
public function reset()
{
foreach ($this->handlers as $handler) {
if ($handler instanceof ResettableInterface) {
$handler->reset();
}
}
foreach ($this->processors as $processor) {
if ($processor instanceof ResettableInterface) {
$processor->reset();
}
}
}
public function addDebug($message, array $context = array())
{
return $this->addRecord(static::DEBUG, $message, $context);
}
public function addInfo($message, array $context = array())
{
return $this->addRecord(static::INFO, $message, $context);
}
public function addNotice($message, array $context = array())
{
return $this->addRecord(static::NOTICE, $message, $context);
}
public function addWarning($message, array $context = array())
{
return $this->addRecord(static::WARNING, $message, $context);
}
public function addError($message, array $context = array())
{
return $this->addRecord(static::ERROR, $message, $context);
}
public function addCritical($message, array $context = array())
{
return $this->addRecord(static::CRITICAL, $message, $context);
}
public function addAlert($message, array $context = array())
{
return $this->addRecord(static::ALERT, $message, $context);
}
public function addEmergency($message, array $context = array())
{
return $this->addRecord(static::EMERGENCY, $message, $context);
}
public static function getLevels()
{
return array_flip(static::$levels);
}
public static function getLevelName($level)
{
if (!isset(static::$levels[$level])) {
throw new InvalidArgumentException('Level "'.$level.'" is not defined, use one of: '.implode(', ', array_keys(static::$levels)));
}
return static::$levels[$level];
}
public static function toMonologLevel($level)
{
if (is_string($level) && defined(__CLASS__.'::'.strtoupper($level))) {
return constant(__CLASS__.'::'.strtoupper($level));
}
return $level;
}
public function isHandling($level)
{
$record = array('level'=> $level,
);
foreach ($this->handlers as $handler) {
if ($handler->isHandling($record)) {
return true;
}
}
return false;
}
public function setExceptionHandler($callback)
{
if (!is_callable($callback)) {
throw new \InvalidArgumentException('Exception handler must be valid callable (callback or object with an __invoke method), '.var_export($callback, true).' given');
}
$this->exceptionHandler = $callback;
return $this;
}
public function getExceptionHandler()
{
return $this->exceptionHandler;
}
protected function handleException(Exception $e, array $record)
{
if (!$this->exceptionHandler) {
throw $e;
}
call_user_func($this->exceptionHandler, $e, $record);
}
public function log($level, $message, array $context = array())
{
$level = static::toMonologLevel($level);
return $this->addRecord($level, $message, $context);
}
public function debug($message, array $context = array())
{
return $this->addRecord(static::DEBUG, $message, $context);
}
public function info($message, array $context = array())
{
return $this->addRecord(static::INFO, $message, $context);
}
public function notice($message, array $context = array())
{
return $this->addRecord(static::NOTICE, $message, $context);
}
public function warn($message, array $context = array())
{
return $this->addRecord(static::WARNING, $message, $context);
}
public function warning($message, array $context = array())
{
return $this->addRecord(static::WARNING, $message, $context);
}
public function err($message, array $context = array())
{
return $this->addRecord(static::ERROR, $message, $context);
}
public function error($message, array $context = array())
{
return $this->addRecord(static::ERROR, $message, $context);
}
public function crit($message, array $context = array())
{
return $this->addRecord(static::CRITICAL, $message, $context);
}
public function critical($message, array $context = array())
{
return $this->addRecord(static::CRITICAL, $message, $context);
}
public function alert($message, array $context = array())
{
return $this->addRecord(static::ALERT, $message, $context);
}
public function emerg($message, array $context = array())
{
return $this->addRecord(static::EMERGENCY, $message, $context);
}
public function emergency($message, array $context = array())
{
return $this->addRecord(static::EMERGENCY, $message, $context);
}
public static function setTimezone(\DateTimeZone $tz)
{
self::$timezone = $tz;
}
}
}
namespace Symfony\Component\HttpKernel\Log
{
use Psr\Log\LoggerInterface as PsrLogger;
interface LoggerInterface extends PsrLogger
{
public function emerg($message, array $context = array());
public function crit($message, array $context = array());
public function err($message, array $context = array());
public function warn($message, array $context = array());
}
}
namespace Symfony\Component\HttpKernel\Log
{
interface DebugLoggerInterface
{
public function getLogs();
public function countErrors();
}
}
namespace Symfony\Bridge\Monolog
{
use Monolog\Logger as BaseLogger;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
class Logger extends BaseLogger implements LoggerInterface, DebugLoggerInterface
{
public function emerg($message, array $context = array())
{
return parent::addRecord(BaseLogger::EMERGENCY, $message, $context);
}
public function crit($message, array $context = array())
{
return parent::addRecord(BaseLogger::CRITICAL, $message, $context);
}
public function err($message, array $context = array())
{
return parent::addRecord(BaseLogger::ERROR, $message, $context);
}
public function warn($message, array $context = array())
{
return parent::addRecord(BaseLogger::WARNING, $message, $context);
}
public function getLogs()
{
if ($logger = $this->getDebugLogger()) {
return $logger->getLogs();
}
return array();
}
public function countErrors()
{
if ($logger = $this->getDebugLogger()) {
return $logger->countErrors();
}
return 0;
}
private function getDebugLogger()
{
foreach ($this->handlers as $handler) {
if ($handler instanceof DebugLoggerInterface) {
return $handler;
}
}
}
}
}
namespace Monolog\Handler\FingersCrossed
{
interface ActivationStrategyInterface
{
public function isHandlerActivated(array $record);
}
}
namespace Monolog\Handler\FingersCrossed
{
use Monolog\Logger;
class ErrorLevelActivationStrategy implements ActivationStrategyInterface
{
private $actionLevel;
public function __construct($actionLevel)
{
$this->actionLevel = Logger::toMonologLevel($actionLevel);
}
public function isHandlerActivated(array $record)
{
return $record['level'] >= $this->actionLevel;
}
}
}
namespace Doctrine\Common\Lexer
{
abstract class AbstractLexer
{
private $input;
private $tokens = array();
private $position = 0;
private $peek = 0;
public $lookahead;
public $token;
public function setInput($input)
{
$this->input = $input;
$this->tokens = array();
$this->reset();
$this->scan($input);
}
public function reset()
{
$this->lookahead = null;
$this->token = null;
$this->peek = 0;
$this->position = 0;
}
public function resetPeek()
{
$this->peek = 0;
}
public function resetPosition($position = 0)
{
$this->position = $position;
}
public function getInputUntilPosition($position)
{
return substr($this->input, 0, $position);
}
public function isNextToken($token)
{
return null !== $this->lookahead && $this->lookahead['type'] === $token;
}
public function isNextTokenAny(array $tokens)
{
return null !== $this->lookahead && in_array($this->lookahead['type'], $tokens, true);
}
public function moveNext()
{
$this->peek = 0;
$this->token = $this->lookahead;
$this->lookahead = (isset($this->tokens[$this->position]))
? $this->tokens[$this->position++] : null;
return $this->lookahead !== null;
}
public function skipUntil($type)
{
while ($this->lookahead !== null && $this->lookahead['type'] !== $type) {
$this->moveNext();
}
}
public function isA($value, $token)
{
return $this->getType($value) === $token;
}
public function peek()
{
if (isset($this->tokens[$this->position + $this->peek])) {
return $this->tokens[$this->position + $this->peek++];
} else {
return null;
}
}
public function glimpse()
{
$peek = $this->peek();
$this->peek = 0;
return $peek;
}
protected function scan($input)
{
static $regex;
if ( ! isset($regex)) {
$regex = sprintf('/(%s)|%s/%s',
implode(')|(', $this->getCatchablePatterns()),
implode('|', $this->getNonCatchablePatterns()),
$this->getModifiers()
);
}
$flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_OFFSET_CAPTURE;
$matches = preg_split($regex, $input, -1, $flags);
if (false === $matches) {
$matches = array(array($input, 0));
}
foreach ($matches as $match) {
$type = $this->getType($match[0]);
$this->tokens[] = array('value'=> $match[0],'type'=> $type,'position'=> $match[1],
);
}
}
public function getLiteral($token)
{
$className = get_class($this);
$reflClass = new \ReflectionClass($className);
$constants = $reflClass->getConstants();
foreach ($constants as $name => $value) {
if ($value === $token) {
return $className .'::'. $name;
}
}
return $token;
}
protected function getModifiers()
{
return'i';
}
abstract protected function getCatchablePatterns();
abstract protected function getNonCatchablePatterns();
abstract protected function getType(&$value);
}
}
namespace Doctrine\Common\Annotations
{
use Doctrine\Common\Lexer\AbstractLexer;
final class DocLexer extends AbstractLexer
{
const T_NONE = 1;
const T_INTEGER = 2;
const T_STRING = 3;
const T_FLOAT = 4;
const T_IDENTIFIER = 100;
const T_AT = 101;
const T_CLOSE_CURLY_BRACES = 102;
const T_CLOSE_PARENTHESIS = 103;
const T_COMMA = 104;
const T_EQUALS = 105;
const T_FALSE = 106;
const T_NAMESPACE_SEPARATOR = 107;
const T_OPEN_CURLY_BRACES = 108;
const T_OPEN_PARENTHESIS = 109;
const T_TRUE = 110;
const T_NULL = 111;
const T_COLON = 112;
const T_MINUS = 113;
protected $noCase = array('@'=> self::T_AT,','=> self::T_COMMA,'('=> self::T_OPEN_PARENTHESIS,')'=> self::T_CLOSE_PARENTHESIS,'{'=> self::T_OPEN_CURLY_BRACES,'}'=> self::T_CLOSE_CURLY_BRACES,'='=> self::T_EQUALS,':'=> self::T_COLON,'-'=> self::T_MINUS,'\\'=> self::T_NAMESPACE_SEPARATOR
);
protected $withCase = array('true'=> self::T_TRUE,'false'=> self::T_FALSE,'null'=> self::T_NULL
);
public function nextTokenIsAdjacent() : bool
{
return $this->token === null
|| ($this->lookahead !== null
&& ($this->lookahead['position'] - $this->token['position']) === strlen($this->token['value']));
}
protected function getCatchablePatterns()
{
return array('[a-z_\\\][a-z0-9_\:\\\]*[a-z_][a-z0-9_]*','(?:[+-]?[0-9]+(?:[\.][0-9]+)*)(?:[eE][+-]?[0-9]+)?','"(?:""|[^"])*+"',
);
}
protected function getNonCatchablePatterns()
{
return array('\s+','\*+','(.)');
}
protected function getType(&$value)
{
$type = self::T_NONE;
if ($value[0] ==='"') {
$value = str_replace('""','"', substr($value, 1, strlen($value) - 2));
return self::T_STRING;
}
if (isset($this->noCase[$value])) {
return $this->noCase[$value];
}
if ($value[0] ==='_'|| $value[0] ==='\\'|| ctype_alpha($value[0])) {
return self::T_IDENTIFIER;
}
$lowerValue = strtolower($value);
if (isset($this->withCase[$lowerValue])) {
return $this->withCase[$lowerValue];
}
if (is_numeric($value)) {
return (strpos($value,'.') !== false || stripos($value,'e') !== false)
? self::T_FLOAT : self::T_INTEGER;
}
return $type;
}
}
}
namespace Doctrine\Common\Annotations
{
interface Reader
{
function getClassAnnotations(\ReflectionClass $class);
function getClassAnnotation(\ReflectionClass $class, $annotationName);
function getMethodAnnotations(\ReflectionMethod $method);
function getMethodAnnotation(\ReflectionMethod $method, $annotationName);
function getPropertyAnnotations(\ReflectionProperty $property);
function getPropertyAnnotation(\ReflectionProperty $property, $annotationName);
}
}
namespace Doctrine\Common\Annotations
{
class FileCacheReader implements Reader
{
private $reader;
private $dir;
private $debug;
private $loadedAnnotations = array();
private $classNameHashes = array();
private $umask;
public function __construct(Reader $reader, $cacheDir, $debug = false, $umask = 0002)
{
if ( ! is_int($umask)) {
throw new \InvalidArgumentException(sprintf('The parameter umask must be an integer, was: %s',
gettype($umask)
));
}
$this->reader = $reader;
$this->umask = $umask;
if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0777 & (~$this->umask), true)) {
throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist and could not be created.', $cacheDir));
}
$this->dir = rtrim($cacheDir,'\\/');
$this->debug = $debug;
}
public function getClassAnnotations(\ReflectionClass $class)
{
if ( ! isset($this->classNameHashes[$class->name])) {
$this->classNameHashes[$class->name] = sha1($class->name);
}
$key = $this->classNameHashes[$class->name];
if (isset($this->loadedAnnotations[$key])) {
return $this->loadedAnnotations[$key];
}
$path = $this->dir.'/'.strtr($key,'\\','-').'.cache.php';
if (!is_file($path)) {
$annot = $this->reader->getClassAnnotations($class);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
if ($this->debug
&& (false !== $filename = $class->getFileName())
&& filemtime($path) < filemtime($filename)) {
@unlink($path);
$annot = $this->reader->getClassAnnotations($class);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
return $this->loadedAnnotations[$key] = include $path;
}
public function getPropertyAnnotations(\ReflectionProperty $property)
{
$class = $property->getDeclaringClass();
if ( ! isset($this->classNameHashes[$class->name])) {
$this->classNameHashes[$class->name] = sha1($class->name);
}
$key = $this->classNameHashes[$class->name].'$'.$property->getName();
if (isset($this->loadedAnnotations[$key])) {
return $this->loadedAnnotations[$key];
}
$path = $this->dir.'/'.strtr($key,'\\','-').'.cache.php';
if (!is_file($path)) {
$annot = $this->reader->getPropertyAnnotations($property);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
if ($this->debug
&& (false !== $filename = $class->getFilename())
&& filemtime($path) < filemtime($filename)) {
@unlink($path);
$annot = $this->reader->getPropertyAnnotations($property);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
return $this->loadedAnnotations[$key] = include $path;
}
public function getMethodAnnotations(\ReflectionMethod $method)
{
$class = $method->getDeclaringClass();
if ( ! isset($this->classNameHashes[$class->name])) {
$this->classNameHashes[$class->name] = sha1($class->name);
}
$key = $this->classNameHashes[$class->name].'#'.$method->getName();
if (isset($this->loadedAnnotations[$key])) {
return $this->loadedAnnotations[$key];
}
$path = $this->dir.'/'.strtr($key,'\\','-').'.cache.php';
if (!is_file($path)) {
$annot = $this->reader->getMethodAnnotations($method);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
if ($this->debug
&& (false !== $filename = $class->getFilename())
&& filemtime($path) < filemtime($filename)) {
@unlink($path);
$annot = $this->reader->getMethodAnnotations($method);
$this->saveCacheFile($path, $annot);
return $this->loadedAnnotations[$key] = $annot;
}
return $this->loadedAnnotations[$key] = include $path;
}
private function saveCacheFile($path, $data)
{
if (!is_writable($this->dir)) {
throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable. Both, the webserver and the console user need access. You can manage access rights for multiple users with "chmod +a". If your system does not support this, check out the acl package.', $this->dir));
}
$tempfile = tempnam($this->dir, uniqid('', true));
if (false === $tempfile) {
throw new \RuntimeException(sprintf('Unable to create tempfile in directory: %s', $this->dir));
}
@chmod($tempfile, 0666 & (~$this->umask));
$written = file_put_contents($tempfile,'<?php return unserialize('.var_export(serialize($data), true).');');
if (false === $written) {
throw new \RuntimeException(sprintf('Unable to write cached file to: %s', $tempfile));
}
@chmod($tempfile, 0666 & (~$this->umask));
if (false === rename($tempfile, $path)) {
@unlink($tempfile);
throw new \RuntimeException(sprintf('Unable to rename %s to %s', $tempfile, $path));
}
}
public function getClassAnnotation(\ReflectionClass $class, $annotationName)
{
$annotations = $this->getClassAnnotations($class);
foreach ($annotations as $annotation) {
if ($annotation instanceof $annotationName) {
return $annotation;
}
}
return null;
}
public function getMethodAnnotation(\ReflectionMethod $method, $annotationName)
{
$annotations = $this->getMethodAnnotations($method);
foreach ($annotations as $annotation) {
if ($annotation instanceof $annotationName) {
return $annotation;
}
}
return null;
}
public function getPropertyAnnotation(\ReflectionProperty $property, $annotationName)
{
$annotations = $this->getPropertyAnnotations($property);
foreach ($annotations as $annotation) {
if ($annotation instanceof $annotationName) {
return $annotation;
}
}
return null;
}
public function clearLoadedAnnotations()
{
$this->loadedAnnotations = array();
}
}
}
namespace Doctrine\Common\Annotations
{
use SplFileObject;
final class PhpParser
{
public function parseClass(\ReflectionClass $class)
{
if (method_exists($class,'getUseStatements')) {
return $class->getUseStatements();
}
if (false === $filename = $class->getFileName()) {
return array();
}
$content = $this->getFileContent($filename, $class->getStartLine());
if (null === $content) {
return array();
}
$namespace = preg_quote($class->getNamespaceName());
$content = preg_replace('/^.*?(\bnamespace\s+'. $namespace .'\s*[;{].*)$/s','\\1', $content);
$tokenizer = new TokenParser('<?php '. $content);
$statements = $tokenizer->parseUseStatements($class->getNamespaceName());
return $statements;
}
private function getFileContent($filename, $lineNumber)
{
if ( ! is_file($filename)) {
return null;
}
$content ='';
$lineCnt = 0;
$file = new SplFileObject($filename);
while (!$file->eof()) {
if ($lineCnt++ == $lineNumber) {
break;
}
$content .= $file->fgets();
}
return $content;
}
}
}
namespace Doctrine\Common
{
use Doctrine\Common\Lexer\AbstractLexer;
abstract class Lexer extends AbstractLexer
{
}
}
namespace Doctrine\Common\Persistence
{
interface ConnectionRegistry
{
public function getDefaultConnectionName();
public function getConnection($name = null);
public function getConnections();
public function getConnectionNames();
}
}
namespace Doctrine\Common\Persistence
{
interface Proxy
{
const MARKER ='__CG__';
const MARKER_LENGTH = 6;
public function __load();
public function __isInitialized();
}
}
namespace Doctrine\Common\Util
{
use Doctrine\Common\Persistence\Proxy;
class ClassUtils
{
public static function getRealClass($class)
{
if (false === $pos = strrpos($class,'\\'.Proxy::MARKER.'\\')) {
return $class;
}
return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
}
public static function getClass($object)
{
return self::getRealClass(get_class($object));
}
public static function getParentClass($className)
{
return get_parent_class( self::getRealClass( $className ) );
}
public static function newReflectionClass($class)
{
return new \ReflectionClass( self::getRealClass( $class ) );
}
public static function newReflectionObject($object)
{
return self::newReflectionClass( self::getClass( $object ) );
}
public static function generateProxyClassName($className, $proxyNamespace)
{
return rtrim($proxyNamespace,'\\') .'\\'.Proxy::MARKER.'\\'. ltrim($className,'\\');
}
}
}
namespace Doctrine\Common\Persistence
{
interface ManagerRegistry extends ConnectionRegistry
{
public function getDefaultManagerName();
public function getManager($name = null);
public function getManagers();
public function resetManager($name = null);
public function getAliasNamespace($alias);
public function getManagerNames();
public function getRepository($persistentObject, $persistentManagerName = null);
public function getManagerForClass($class);
}
}
namespace Symfony\Bridge\Doctrine
{
use Doctrine\Common\Persistence\ManagerRegistry as ManagerRegistryInterface;
use Doctrine\ORM\EntityManager;
interface RegistryInterface extends ManagerRegistryInterface
{
public function getDefaultEntityManagerName();
public function getEntityManager($name = null);
public function getEntityManagers();
public function resetEntityManager($name = null);
public function getEntityNamespace($alias);
public function getEntityManagerNames();
public function getEntityManagerForClass($class);
}
}
namespace Doctrine\Common\Persistence
{
abstract class AbstractManagerRegistry implements ManagerRegistry
{
private $name;
private $connections;
private $managers;
private $defaultConnection;
private $defaultManager;
private $proxyInterfaceName;
public function __construct($name, array $connections, array $managers, $defaultConnection, $defaultManager, $proxyInterfaceName)
{
$this->name = $name;
$this->connections = $connections;
$this->managers = $managers;
$this->defaultConnection = $defaultConnection;
$this->defaultManager = $defaultManager;
$this->proxyInterfaceName = $proxyInterfaceName;
}
abstract protected function getService($name);
abstract protected function resetService($name);
public function getName()
{
return $this->name;
}
public function getConnection($name = null)
{
if (null === $name) {
$name = $this->defaultConnection;
}
if (!isset($this->connections[$name])) {
throw new \InvalidArgumentException(sprintf('Doctrine %s Connection named "%s" does not exist.', $this->name, $name));
}
return $this->getService($this->connections[$name]);
}
public function getConnectionNames()
{
return $this->connections;
}
public function getConnections()
{
$connections = [];
foreach ($this->connections as $name => $id) {
$connections[$name] = $this->getService($id);
}
return $connections;
}
public function getDefaultConnectionName()
{
return $this->defaultConnection;
}
public function getDefaultManagerName()
{
return $this->defaultManager;
}
public function getManager($name = null)
{
if (null === $name) {
$name = $this->defaultManager;
}
if (!isset($this->managers[$name])) {
throw new \InvalidArgumentException(sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name));
}
return $this->getService($this->managers[$name]);
}
public function getManagerForClass($class)
{
if (strpos($class,':') !== false) {
list($namespaceAlias, $simpleClassName) = explode(':', $class, 2);
$class = $this->getAliasNamespace($namespaceAlias) .'\\'. $simpleClassName;
}
$proxyClass = new \ReflectionClass($class);
if ($proxyClass->implementsInterface($this->proxyInterfaceName)) {
if (! $parentClass = $proxyClass->getParentClass()) {
return null;
}
$class = $parentClass->getName();
}
foreach ($this->managers as $id) {
$manager = $this->getService($id);
if (!$manager->getMetadataFactory()->isTransient($class)) {
return $manager;
}
}
}
public function getManagerNames()
{
return $this->managers;
}
public function getManagers()
{
$dms = [];
foreach ($this->managers as $name => $id) {
$dms[$name] = $this->getService($id);
}
return $dms;
}
public function getRepository($persistentObjectName, $persistentManagerName = null)
{
return $this->getManager($persistentManagerName)->getRepository($persistentObjectName);
}
public function resetManager($name = null)
{
if (null === $name) {
$name = $this->defaultManager;
}
if (!isset($this->managers[$name])) {
throw new \InvalidArgumentException(sprintf('Doctrine %s Manager named "%s" does not exist.', $this->name, $name));
}
$this->resetService($this->managers[$name]);
return $this->getManager($name);
}
}
}
namespace Symfony\Bridge\Doctrine
{
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
abstract class ManagerRegistry extends AbstractManagerRegistry implements ContainerAwareInterface
{
protected $container;
protected function getService($name)
{
return $this->container->get($name);
}
protected function resetService($name)
{
$this->container->set($name, null);
}
public function setContainer(ContainerInterface $container = null)
{
$this->container = $container;
}
}
}
namespace Doctrine\Bundle\DoctrineBundle
{
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\EntityManager;
class Registry extends ManagerRegistry implements RegistryInterface
{
public function __construct(ContainerInterface $container, array $connections, array $entityManagers, $defaultConnection, $defaultEntityManager)
{
$this->setContainer($container);
parent::__construct('ORM', $connections, $entityManagers, $defaultConnection, $defaultEntityManager,'Doctrine\ORM\Proxy\Proxy');
}
public function getDefaultEntityManagerName()
{
trigger_error('getDefaultEntityManagerName is deprecated since Symfony 2.1. Use getDefaultManagerName instead', E_USER_DEPRECATED);
return $this->getDefaultManagerName();
}
public function getEntityManager($name = null)
{
trigger_error('getEntityManager is deprecated since Symfony 2.1. Use getManager instead', E_USER_DEPRECATED);
return $this->getManager($name);
}
public function getEntityManagers()
{
trigger_error('getEntityManagers is deprecated since Symfony 2.1. Use getManagers instead', E_USER_DEPRECATED);
return $this->getManagers();
}
public function resetEntityManager($name = null)
{
trigger_error('resetEntityManager is deprecated since Symfony 2.1. Use resetManager instead', E_USER_DEPRECATED);
$this->resetManager($name);
}
public function getEntityNamespace($alias)
{
trigger_error('getEntityNamespace is deprecated since Symfony 2.1. Use getAliasNamespace instead', E_USER_DEPRECATED);
return $this->getAliasNamespace($alias);
}
public function getAliasNamespace($alias)
{
foreach (array_keys($this->getManagers()) as $name) {
try {
return $this->getManager($name)->getConfiguration()->getEntityNamespace($alias);
} catch (ORMException $e) {
}
}
throw ORMException::unknownEntityNamespace($alias);
}
public function getEntityManagerNames()
{
trigger_error('getEntityManagerNames is deprecated since Symfony 2.1. Use getManagerNames instead', E_USER_DEPRECATED);
return $this->getManagerNames();
}
public function getEntityManagerForClass($class)
{
trigger_error('getEntityManagerForClass is deprecated since Symfony 2.1. Use getManagerForClass instead', E_USER_DEPRECATED);
return $this->getManagerForClass($class);
}
}
}