@component('mail::message')

# Welcome to HireReady UAE! 🎉

Hi **{{ $user->name }}**,

Thank you for joining **HireReady UAE** — your AI-powered career assistant built for the UAE job market.

Here is what you can do with your free account:

@component('mail::panel')
✅ Upload up to **5 resumes**
✅ Get **ATS scores** for your resume
✅ Generate **cover letters** in seconds
✅ **Match** your resume with job descriptions
✅ Prepare for **interviews** with AI-generated questions
✅ Track all your **job applications** in one place
@endcomponent

@component('mail::button', ['url' => config('app.frontend_url') . '/dashboard', 'color' => 'primary'])
Go to Dashboard
@endcomponent

If you have any questions, just reply to this email — we are happy to help!

Good luck with your job search in the UAE 🇦🇪

Thanks,
**The HireReady UAE Team**

@component('mail::subcopy')
If you did not create an account, you can safely ignore this email.
@endcomponent

@endcomponent