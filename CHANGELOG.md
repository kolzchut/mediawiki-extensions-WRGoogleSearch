Changelog
==========

1.2.0 2019-01-03
----
Improved analytics:

- Prevent extension:GoogleUniversalAnalytics from automatically sending a page view hit, because CSE
is also sending one out.
- Push a search event to Google Tag Manager's dataLayer

1.1.1 (unknown date?)
I have no idea what took place here, but apparently I released such a version.

1.1.0 2016-05-02
-----
New option to instruct Google to add a sitelinks search box (in Google's own search results).

1.0.1
-----
Do not take over inputboxes with class 'internalSearch'. This allows
extension:SearchBoxes to maintain its search destinations.

1.0.0
-----
A much improved version of Google Search:

- No additional search form in the special page;
  the sidebar search is now used for it.
- No loading of Google JS in regular pages;
  we now just send the query to the special page
- Page icon is Google CSE instead of a generic one,
  in order to give proper credit.

0.1.3
-----
The original version, based on Extension:GoogleSiteSearch (v2.0) by Ryan Finnie.
