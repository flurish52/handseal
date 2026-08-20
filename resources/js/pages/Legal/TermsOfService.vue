<script setup>
import { Head, Link } from '@inertiajs/vue3';
import SiteNav from '@/Components/Landing/SiteNav.vue';
import SiteFooter from '@/Components/Landing/SiteFooter.vue';

defineProps({
    isAuthenticated: { type: Boolean, default: false },
    canRegister: { type: Boolean, default: true },
});
</script>

<template>
    <Head title="Terms of Service" />

    <div class="bg-seal-paper font-sans text-seal-ink antialiased">
        <SiteNav :is-authenticated="isAuthenticated" :can-register="canRegister" />

        <main class="mx-auto max-w-3xl px-6 py-16">
            <p class="text-sm font-medium uppercase tracking-wide text-seal-brass">Legal</p>
            <h1 class="mt-2 font-serif text-3xl font-semibold text-seal-ink">Terms of Service</h1>
            <p class="mt-2 text-sm text-seal-muted">Last updated: {{ new Date().toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' }) }}</p>

            <div class="prose prose-slate mt-10 max-w-none prose-headings:font-serif prose-headings:text-seal-ink prose-a:text-seal-brass">

                <p>
                    These Terms of Service ("Terms") govern your access to and use of HandSeal
                    ("HandSeal", "we", "us", or "our"), a certificate issuance and verification
                    platform for training providers, trade schools, and vocational businesses
                    ("Businesses"). By creating an account, issuing certificates, or verifying a
                    certificate through HandSeal, you agree to these Terms.
                </p>

                <h2>1. Who can use HandSeal</h2>
                <p>
                    HandSeal is intended for use by businesses and organisations that train,
                    certify, or credential individuals ("Recipients"), and by members of the
                    public who wish to verify a certificate issued through the platform. You must
                    be at least 18 years old, or the age of majority in your jurisdiction, to
                    create a Business account.
                </p>

                <h2>2. Accounts</h2>
                <p>
                    You may create an account using an email and password, or by signing in with
                    Google. You are responsible for maintaining the confidentiality of your
                    account credentials and for all activity under your account. You must notify
                    us promptly of any unauthorised use of your account.
                </p>

                <h2>3. Certificate issuance and accuracy</h2>
                <p>
                    Businesses are solely responsible for the accuracy of the information they
                    enter when issuing a certificate, including the Recipient's name, the
                    programme or qualification completed, and any dates or credentials listed.
                    HandSeal provides the platform and infrastructure for generating, storing, and
                    verifying certificates, but does not independently verify the truthfulness of
                    the underlying claims made by a Business.
                </p>
                <p>
                    Certificates issued through HandSeal are generated as PDF documents and
                    assigned a unique certificate number in the format
                    <code>HS-[INITIALS][BUSINESS_ID]-[CERT_ID]</code>, which can be looked up
                    through our public verification tool. Businesses agree not to issue
                    certificates for programmes, achievements, or individuals that did not
                    genuinely complete the stated requirements.
                </p>

                <h2>4. Public verification</h2>
                <p>
                    Certificate verification pages are publicly accessible by design, so that any
                    third party (such as an employer) can confirm a certificate's authenticity by
                    its certificate number. Businesses acknowledge that certain certificate details
                    entered at issuance will be visible to anyone performing a lookup.
                </p>

                <h2>5. Payments and subscriptions</h2>
                <p>
                    Paid features of HandSeal are billed through Paystack, a third-party payment
                    processor. By subscribing to a paid plan or making a payment, you agree to
                    Paystack's applicable terms in addition to these Terms. HandSeal does not
                    store your full card details; payment information is handled directly by
                    Paystack. Fees are stated in the currency shown at checkout and are
                    non-refundable except where required by law or expressly stated otherwise.
                </p>

                <h2>6. Referrals</h2>
                <p>
                    HandSeal may offer a referral programme allowing existing users to invite
                    others via a referral code or link. Any rewards associated with referrals are
                    subject to additional terms we may publish separately and may be withdrawn or
                    modified at any time.
                </p>

                <h2>7. Acceptable use</h2>
                <p>You agree not to:</p>
                <ul>
                    <li>Issue fraudulent, misleading, or fabricated certificates;</li>
                    <li>Use HandSeal to impersonate another business or individual;</li>
                    <li>Attempt to circumvent certificate verification or tamper with issued certificates;</li>
                    <li>Interfere with or disrupt the platform, including through automated scraping of verification data at scale;</li>
                    <li>Use the platform in violation of any applicable law.</li>
                </ul>

                <h2>8. Installable app (PWA)</h2>
                <p>
                    HandSeal can be installed as a Progressive Web App on supported devices. When
                    installed, the app functions as a shortcut to the HandSeal web platform and is
                    subject to these same Terms. We are not responsible for how your device or
                    operating system manages installed web apps.
                </p>

                <h2>9. Termination</h2>
                <p>
                    We may suspend or terminate your account if you violate these Terms, including
                    for issuing fraudulent certificates. You may close your account at any time by
                    contacting us; certificates already issued and verified publicly may remain
                    accessible for verification purposes even after account closure, to preserve
                    the integrity of certificates already relied upon by third parties.
                </p>

                <h2>10. Disclaimers</h2>
                <p>
                    HandSeal is provided "as is" without warranties of any kind. We do not
                    guarantee uninterrupted or error-free operation of the platform, including
                    certificate generation, email delivery, or verification lookups.
                </p>

                <h2>11. Limitation of liability</h2>
                <p>
                    To the maximum extent permitted by law, HandSeal shall not be liable for any
                    indirect, incidental, or consequential damages arising from your use of the
                    platform, including damages resulting from a Business's misuse of the
                    certificate issuance features.
                </p>

                <h2>12. Changes to these Terms</h2>
                <p>
                    We may update these Terms from time to time. Continued use of HandSeal after
                    changes take effect constitutes acceptance of the updated Terms.
                </p>

                <h2>13. Contact</h2>
                <p>
                    Questions about these Terms can be sent to
                    <a href="mailto:support@handseal.com">support@handseal.com</a>.
                </p>
            </div>

            <div class="mt-12 border-t border-seal-line pt-6">
                <Link :href="route('privacy.policy')" class="text-sm font-medium text-seal-brass hover:text-seal-navy">
                    Read our Privacy Policy →
                </Link>
            </div>
        </main>

        <SiteFooter />
    </div>
</template>
