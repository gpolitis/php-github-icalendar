<?php

/**
 * Copyright (C) 2015, George Politis https://github.com/gpolitis
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require 'vendor/autoload.php';

// get the access_token and the filter from the incoming request
$in_req = Sabre\HTTP\Sapi::getRequest();
$in_req_query = $in_req->getQueryParameters();

$access_token = isset($in_req_query['access_token'])
    ? $in_req_query['access_token'] : '';
$filter = isset($in_req_query['filter']) ? $in_req_query['filter'] : 'assigned';

// build the request to the GitHub API: https://developer.github.com/v3/issues/
$gh_req_url = sprintf('https://api.github.com/issues?filter=%s',
    rawurlencode($filter));
$gh_req_header_auth = sprintf('token %s', $access_token);
$gh_req_header_ua = 'Sabre\HTTP\Client';

$gh_req = new Sabre\HTTP\Request('GET', $gh_req_url);
$gh_req->setHeader('Authorization', $gh_req_header_auth);
// NOTE the User-Agent header is required to access the GitHub API
$gh_req->setHeader('User-Agent', $gh_req_header_ua);

// send the request and handle the response
$gh_client = new Sabre\HTTP\Client();
$gh_res = $gh_client->send($gh_req);

if ($gh_res->getStatus() != 200) {
    Sabre\HTTP\Sapi::sendResponse($gh_res);
} else {
    $gh_res_body = $gh_res->getBodyAsString();

    // parse the body into issues. the response format is described here
    // https://developer.github.com/v3/issues/
    $issues = json_decode($gh_res_body, true);

    // build the calendar
    $vcalendar = new Sabre\VObject\Component\VCalendar();

    foreach ($issues as $idx => $issue) {
        $uid = sprintf('%s-%s.issue.github.com',
            $issue['number'], $issue['id']);
        $title = sprintf('%s #%s: %s',
            $issue['repository']['name'], $issue['number'], $issue['title']);
        $organizer = sprintf('%s@users.github.com', $issue['user']['login']);
        $created = new \DateTime($issue['created_at']);
        $updated = new \DateTime($issue['updated_at']);

        $vcalendar->add('VTODO', [
            'CREATED' => $created,
            'DESCRIPTION' => $issue['body'],
            'LAST-MODIFIED' => $updated,
            'ORGANIZER' => $organizer,
            'STATUS' => 'NEEDS-ACTION',
            'SUMMARY' => $title,
            'UID' => $uid,
            'URL' => $issue['html_url'],
        ]);
    }

    // write the response
    $out_res = new Sabre\HTTP\Response();
    $out_res->setStatus(200);
    $out_res->setHeader('Content-Type', 'text/calendar');
    $out_res->setBody(
        $vcalendar->serialize()
    );

    Sabre\HTTP\Sapi::sendResponse($out_res);
}
?>
