<x-tixswap-layout>
    <x-slot name="title">About TixSwap</x-slot>
    <x-slot name="navigation">
         <!-- You can add specific navigation for this page if needed -->
    </x-slot>

    <div class="space-y-16">
        <!-- Header Section -->
        <div class="relative text-center">
             <div class="h-64 bg-gray-200 rounded-lg overflow-hidden">
                <!-- Placeholder for image collage -->
             </div>
             <div class="mt-8">
                <h1 class="text-4xl font-bold text-gray-900">Welcome to TixSwap</h1>
                <p class="mt-4 max-w-2xl mx-auto text-lg text-gray-600">
                    TixSwap is your go-to destination for buying and selling event tickets securely and effortlessly. Connect with fellow enthusiasts and never miss an event again!
                </p>
             </div>
        </div>

        <!-- Features Section -->
        <div>
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Features</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center p-6 bg-white rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold">Secure Transactions</h3>
                    <p class="mt-2 text-gray-600">Our platform ensures complete security for all transactions, giving you peace of mind.</p>
                </div>
                <div class="text-center p-6 bg-white rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold">Notifications</h3>
                    <p class="mt-2 text-gray-600">Stay updated with instant notifications about your listings and purchased tickets.</p>
                </div>
                <div class="text-center p-6 bg-white rounded-lg shadow-md">
                    <h3 class="text-xl font-semibold">Community Connect</h3>
                    <p class="mt-2 text-gray-600">Engage with a community of ticket enthusiasts and share your experiences.</p>
                </div>
            </div>
        </div>

        <!-- User Testimonials Section -->
        <div>
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">User Testimonials</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-gray-50 p-6 rounded-lg">
                    <p class="text-gray-600">"TixSwap made buying concert tickets a breeze. The process was seamless and secure!"</p>
                    <p class="mt-4 font-semibold text-right">- Emily R.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <p class="text-gray-600">"Selling my spare tickets was incredibly easy thanks to TixSwap!"</p>
                    <p class="mt-4 font-semibold text-right">- Michael B.</p>
                </div>
                <div class="bg-gray-50 p-6 rounded-lg">
                    <p class="text-gray-600">"A fantastic community of fellow enthusiasts where I can share my event experiences."</p>
                    <p class="mt-4 font-semibold text-right">- Sarah L.</p>
                </div>
            </div>
        </div>

         <!-- Meet Our Team Section -->
        <div>
            <h2 class="text-3xl font-bold text-center text-gray-900 mb-12">Meet Our Team</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="mx-auto h-24 w-24 bg-gray-200 rounded-full"></div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">John Doe</h3>
                    <p class="text-red-600">CEO</p>
                </div>
                 <div class="text-center">
                    <div class="mx-auto h-24 w-24 bg-gray-200 rounded-full"></div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Jane Smith</h3>
                    <p class="text-red-600">CTO</p>
                </div>
                 <div class="text-center">
                    <div class="mx-auto h-24 w-24 bg-gray-200 rounded-full"></div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Emily Johnson</h3>
                    <p class="text-red-600">CMO</p>
                </div>
                 <div class="text-center">
                    <div class="mx-auto h-24 w-24 bg-gray-200 rounded-full"></div>
                    <h3 class="mt-4 text-lg font-medium text-gray-900">Robert Brown</h3>
                    <p class="text-red-600">Lead Developer</p>
                </div>
            </div>
        </div>

    </div>
</x-tixswap-layout>