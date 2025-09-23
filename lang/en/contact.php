<?php

return [
    'title' => 'Contact',
    'breadcrumb_home' => 'Home',
    'breadcrumb_contact' => 'Contact',
    
    'form' => [
        'title' => 'Ready to Get Started?',
        'name_placeholder' => 'Your name',
        'name_error' => 'Please enter your name',
        'email_placeholder' => 'Your email address',
        'email_error' => 'Please enter your email',
        'phone_placeholder' => 'Your phone number',
        'phone_error' => 'Please enter your phone number',
        'message_placeholder' => 'Write your message...',
        'message_error' => 'Please enter your message',
        'submit_button' => 'Send Message',
        'submitting' => 'Sending...',
    ],
    
    'info' => [
        'title' => 'Here to Help',
        'location_label' => 'Location:',
        'location_value' => 'Wonder Street, USA, New York',
        'call_label' => 'Call Us:',
        'email_label' => 'Email Us:',
        'fax_label' => 'Fax:',
        'hours_title' => 'Opening Hours:',
        'monday' => 'Monday:',
        'tuesday' => 'Tuesday:',
        'wednesday' => 'Wednesday:',
        'thursday' => 'Thursday:',
        'friday' => 'Friday:',
        'closed' => 'Closed',
        'hours' => '8AM - 6AM',
    ],
    
    'validation' => [
        'name_required' => 'Please enter your name.',
        'name_min' => 'Name must be at least 2 characters long.',
        'name_max' => 'Name cannot exceed 255 characters.',
        'email_required' => 'Please enter your email address.',
        'email_invalid' => 'Please enter a valid email address.',
        'email_max' => 'Email cannot exceed 255 characters.',
        'phone_required' => 'Please enter your phone number.',
        'phone_digits' => 'Phone number must contain only digits.',
        'phone_min' => 'Phone number must be at least 10 digits.',
        'phone_max' => 'Phone number cannot exceed 15 digits.',
        'message_required' => 'Please enter your message.',
        'message_min' => 'Message must be at least 10 characters long.',
        'message_max' => 'Message cannot exceed 1000 characters.',
    ],
    
    'messages' => [
        'success' => 'Thank you for your message! We will get back to you soon.',
        'error' => 'Sorry, there was an error sending your message. Please try again.',
    ],
];
