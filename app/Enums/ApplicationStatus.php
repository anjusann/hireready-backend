<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case Applied = 'applied';
    case UnderReview = 'under_review';
    case InterviewScheduled = 'interview_scheduled';
    case FinalInterview = 'final_interview';
    case OfferReceived = 'offer_received';
    case Rejected = 'rejected';
    case Withdrawn = 'withdrawn';
}
