# Using the Cron System

Core Plus has a rudimentary but effective cron system that's built on top of the hook system.  There are four cron hooks builtin: `hourly`, `daily`, `weekly`, and `monthly`.

These must be installed into the web system's internal cron system, as it is not kicked off natively.  There are instructions in the site admin to install this.

To make use of a cron, register a public static function to `/cron/hourly`, `/cron/daily`, `/cron/weekly`, or `/cron/monthly` as necessary in your component.xml.

    <hook name="/cron/daily" call="SomeClass::HookDaily"/>
    
This public static function **must** return either `true` or `false`, (depending on failure or success), and echo any message to be recorded in the cronlog.  _Output is never actually sent to the screen._