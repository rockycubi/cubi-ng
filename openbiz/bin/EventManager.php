<?PHP

/**
 * PHPOpenBiz Framework
 *
 * LICENSE
 *
 * This source file is subject to the BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * @package   openbiz.bin
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @license   http://www.opensource.org/licenses/bsd-license.php
 * @link      http://www.phpopenbiz.org/
 * @version   $Id$
 */

interface iEventManager 
{
	public function trigger($event_key, $target, $params);
	public function attach($event_key, $observer, $priority=null);
}

interface iEvent
{
	public function getName();
	public function getTarget();
	public function getParams();
}

interface iEventObserver
{
	public function observe($event);
}
 
/**
 * EventManager is the class that trigger events
 *
 * @package   openbiz.bin
 * @author    Rocky Swen <rocky@phpopenbiz.org>
 * @copyright Copyright (c) 2005-2011, Rocky Swen
 * @access    public
 */
class EventManager implements iEventManager
{
	protected $eventObsevers;
	
	public function trigger($event_key, $target, $params)
	{
		$event = new Event($event_key, $target, $params);
		$matchedObservers = $this->getMatchObservers($event_key);
		foreach ($matchedObservers as $observer) {
			$observer->observe($event);
		}
	}
	
	public function attach($event_key, $observer, $priority=null)
	{
		$this->eventObsevers[$event_key][] = $observer;
	}
	
	protected function getMatchObservers($event_key)
	{
		if (isset($this->eventObsevers[$event_key])) 
			return $this->eventObsevers[$event_key];
		else 
			return array();
	}
}

class Event implements iEvent
{
	public $event_key, $target, $params;
	public function __construct($event_key, $target, $params)
	{
		$this->event_key = $event_key;
		$this->target = $target;
		$this->params = $params;
	}
	public function getName() { return $this->event_key; }
	public function getTarget() { return $this->target; }
	public function getParams() { return $this->params; }
}

class EventObserver implements iEventObserver
{
	public function observe($event)
	{
		// do something here
	}
}
?>