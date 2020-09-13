<?php

declare(strict_types=1);

namespace EventCalendar\Simple;

require __DIR__ . '/../../bootstrap.php';

use EventCalendar\Translator;
use Tester\DomQuery;
use Tester\Assert;

/**
 * @testCase
 */
final class EventCalendarTest extends \Tester\TestCase
{
    use \Testbench\TComponent;

    private EventCalendar $calendar;
    
    protected function setUp()
    {
        if (!isset($this->calendar)) {
            $this->calendar = new EventCalendar();
            $this->calendar->translator = new Translator();
        }
        $this->attachToPresenter($this->calendar);
    }
    
    public function testStructure()
    {
        $this->calendar->year = 2013;
        $this->calendar->month = 1;
        $html = $this->renderAndReturnHtml();
        $dom = DomQuery::fromHtml($html);
        $noOfValidDays = count($dom->find('.ec-validDay'));
        $noOfEmptyDays = count($dom->find('.ec-empty'));
        Assert::true($noOfValidDays === 31 && $noOfEmptyDays === 4);
    }
    
    public function testMaxLenOfWday()
    {
        $this->calendar->firstDay = EventCalendar::FIRST_MONDAY;
        $this->calendar->options[EventCalendar::OPT_WDAY_MAX_LEN] = 3;
        $html = $this->renderAndReturnHtml();
        $dom = DomQuery::fromHtml($html);
        $wednesElem = $dom->find('.ec-monthTable th');
        $wednesdayName = $wednesElem[2]->asXML();
        Assert::same('Wed', strip_tags($wednesdayName));
    }
    
    public function testDisabledTopNav()
    {
        $this->calendar->options[EventCalendar::OPT_SHOW_TOP_NAV] = false;
        $html = $this->renderAndReturnHtml();
        $dom = DomQuery::fromHtml($html);
        Assert::true(!$dom->has('.ec-monthTable a'));
    }
    
    public function testTexy()
    {
        $this->calendar->year = 2012;
        $this->calendar->month = 2;
        $this->calendar->events = new TestEvent();
        $html = $this->renderAndReturnHtml();
        $dom = DomQuery::fromHtml($html);
        $events = $dom->find('.ec-event');
        $texyOn = class_exists(\Texy::class) && strpos($events[0]->asXML(), 'Custom event with <strong>bold</strong>') !== false;
        $texyOff = !class_exists(\Texy::class) && strpos($events[0]->asXML(), 'Custom event with **bold** text');
        Assert::true($texyOn || $texyOff);
    }
    
    private function renderAndReturnHtml(): string
    {
        ob_start();
        $this->calendar->render();
        return ob_get_clean();
    }
}


$testCase = new EventCalendarTest();
$testCase->run();
