# DScan Aggregator Tool

First - I won't help you unless I've already spoken to you (sorry!). This is here because a few people asked me for the code (probably so they can go re-write it properly for themselves which I haven't had time to do yet). This isn't because I'm an ass, it's because I don't ven want to look at this code and I definitely don't want to support it. Frankly it's not fit for use, and should only be used as a reference to get a vague idea of the operations needed to make a dscan aggregator.

A very simple, very terrible, php dscan aggregator. To be quite clear, this code is horrible, was written when I was jsut learning php, and follows pretty much no code conventions at all. Don't mistake all the comments for 'good documentation.' Quantity =/= Quality, and the only part of that my code has of comments is quantity (msotly ebcause it's so horrible I need help remembering why I did these stupid things).

It should work out of the box on a standard reasonably up to date version of php wihtout any changes.

Configurable things (mostly file storage locations) are couched in comments with lot of questions marks (e.g. // !!!!!!!!!!!!!!!!!!!!) so you can find them easily.

If you want to debug, comment out line 525 (the one that says: header('Location: BLAH BLAH BLAH ) - that will use the test output stuff on the main submit page (the one with the big text area) and lets you get console logs and php errors (I'm fairly sure this kicks out errors even when it's working - I really wasn't joking when I said it was crap code.

If it doesn't work, it's probably one of three things:
1) Lines 72 - 76: if it can't pull in the categories of ships, it won't work (and they aren't sanitised for prescense, so it's actually the loops that spit out errors)
2) Lines 509 - 525: These create the new scan page and navigate you there
3) Permissions error in the scan directory - the php user agent need to have write permissions in that folder (this is more common on servers rather than lcoal dev environments)


==========================================================================================================================================

Free Public License 1.0.0

Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby granted.

THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
