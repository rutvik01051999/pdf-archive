<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CmsPage;

class CmsPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'privacy-policy',
                'title' => 'Privacy Policy',
                'content' => '<h2>Privacy Policy</h2>
                <p>This Privacy Policy describes how we collect, use, and protect your personal information when you use our website.</p>
                
                <h3>Information We Collect</h3>
                <p>We may collect the following types of information:</p>
                <ul>
                    <li>Personal information you provide directly to us</li>
                    <li>Information collected automatically when you use our services</li>
                    <li>Information from third-party sources</li>
                </ul>
                
                <h3>How We Use Your Information</h3>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Provide and improve our services</li>
                    <li>Communicate with you</li>
                    <li>Comply with legal obligations</li>
                </ul>
                
                <h3>Data Protection</h3>
                <p>We implement appropriate security measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction.</p>
                
                <h3>Contact Us</h3>
                <p>If you have any questions about this Privacy Policy, please contact us.</p>',
                'meta_title' => 'Privacy Policy - Our Website',
                'meta_description' => 'Learn about our privacy policy and how we protect your personal information.',
                'is_active' => true
            ],
            [
                'slug' => 'terms-and-conditions',
                'title' => 'Terms and Conditions',
                'content' => '<h2>Terms and Conditions</h2>
                <p>These Terms and Conditions govern your use of our website and services.</p>
                
                <h3>Acceptance of Terms</h3>
                <p>By accessing and using this website, you accept and agree to be bound by the terms and provision of this agreement.</p>
                
                <h3>Use License</h3>
                <p>Permission is granted to temporarily download one copy of the materials on our website for personal, non-commercial transitory viewing only.</p>
                
                <h3>Disclaimer</h3>
                <p>The materials on our website are provided on an "as is" basis. We make no warranties, expressed or implied, and hereby disclaim and negate all other warranties.</p>
                
                <h3>Limitations</h3>
                <p>In no event shall our company or its suppliers be liable for any damages arising out of the use or inability to use the materials on our website.</p>
                
                <h3>Governing Law</h3>
                <p>These terms and conditions are governed by and construed in accordance with the laws and you irrevocably submit to the exclusive jurisdiction of the courts in that state or location.</p>
                
                <h3>Contact Information</h3>
                <p>If you have any questions about these Terms and Conditions, please contact us.</p>',
                'meta_title' => 'Terms and Conditions - Our Website',
                'meta_description' => 'Read our terms and conditions for using our website and services.',
                'is_active' => true
            ]
        ];

        foreach ($pages as $page) {
            CmsPage::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
}
