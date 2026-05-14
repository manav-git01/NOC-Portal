# NOC Request Notification

Hello Higher Faculty,

A student has requested a No Objection Certificate (NOC) for their internship application.

## Application Details

**Student Name:** {{ $application->user->name }}  
**Student Email:** {{ $application->user->email }}  
**Enrollment Number:** {{ $application->user->enrollment_number }}  
**Department:** {{ $application->user->department }}

## Internship Details

**Company Name:** {{ $application->company_name }}  
**Position:** {{ $application->internship_position }}  
**Start Date:** {{ $application->start_date->format('M d, Y') }}  
**End Date:** {{ $application->end_date->format('M d, Y') }}  
**Technology:** {{ $application->technology }}

## Current Status

**Application Status:** Faculty Approved  
**NOC Requested:** Yes

[Review Application]({{ route('higher-faculty.applications.show', $application) }})

Please review the application and approve or reject the NOC request at your earliest convenience.

Thanks,  
{{ config('app.name') }}
