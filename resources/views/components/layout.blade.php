<!doctype html>

<title> Blog</title>
<link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.gstatic.com">
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/1.9.1/tailwind.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/gh/alpinejs/alpine@v2.x.x/dist/alpine.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/2.7.0/alpine.js"></script>
<script>
    function infiniteScroll() {
        return {
            triggerElement: null,
            page: 1,
            lastPage: null,
            itemsPerPage: 10,
            observer: null,
            isObserverPolyfilled: false,
            items: [],
            init(elementId) {
                const ctx = this
                this.triggerElement = document.querySelector(elementId ? elementId : '#infinite-scroll-trigger')

                // Check if browser can use IntersectionObserver which is waaaay more performant
                if (!('IntersectionObserver' in window) ||
                    !('IntersectionObserverEntry' in window) ||
                    !('isIntersecting' in window.IntersectionObserverEntry.prototype) ||
                    !('intersectionRatio' in window.IntersectionObserverEntry.prototype)) {
                    // Loading polyfill since IntersectionObserver is not available
                    this.isObserverPolyfilled = true

                    // Storing function in window so we can wipe it when reached last page
                    window.alpineInfiniteScroll = {
                        scrollFunc() {
                            var position = ctx.triggerElement.getBoundingClientRect()

                            if (position.top < window.innerHeight && position.bottom >= 0) {
                                ctx.getItems()
                            }
                        }
                    }

                    window.addEventListener('scroll', window.alpineInfiniteScroll.scrollFunc)
                } else {
                    // We can access IntersectionObserver
                    this.observer = new IntersectionObserver(function(entries) {
                        if (entries[0].isIntersecting === true) {
                            ctx.getItems()
                        }
                    }, {
                        threshold: [0]
                    })

                    this.observer.observe(this.triggerElement)
                }

                this.getItems()
            },
            getItems() {
                // TODO: Do fetch here for the content and concat it to populated items
                // TODO: Set last page from API call - ceil it

                // SOF: Dummy Data
                this.lastPage = 5
                console.log('Simulating fetching items...')
                let dummyAdd = this.page === 1 ? 1 : 1 + (this.page - 1) * this.itemsPerPage
                this.items = this.items.concat(Array.from({
                    length: this.itemsPerPage
                }, (_, i) => i + dummyAdd))
                // EOF: Dummy Data

                // Next page
                this.page++

                // We have shown the last page - clean up
                if (this.lastPage && this.page > this.lastPage) {
                    if (this.isObserverPolyfilled) {
                        window.removeEventListener('scroll', window.alpineInfiniteScroll.scrollFunc)
                    } else {
                        this.observer.unobserve(this.triggerElement)
                    }

                    this.triggerElement.parentNode.removeChild(this.triggerElement)
                }
            }
        }
    }
</script>

<style>
    html {
        scroll-behavior: smooth;
    }

    .clamp {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .clamp.one-line {
        -webkit-line-clamp: 1;
    }
</style>

<body style="font-family: Open Sans, sans-serif">
    <section class="px-6 py-8">
        <nav class="md:flex md:justify-between md:items-center">
            <div>
                <a href="/">
                    <h1 class="text-2xl">BLOG</h1>
                </a>
            </div>

            <div class="mt-8 md:mt-0 flex items-center">
                @auth
                    <x-dropdown>
                        <x-slot name="trigger">
                            <button class="text-xs font-bold uppercase">
                                Welcome, {{ auth()->user()->name }}!
                            </button>
                        </x-slot>

                        @admin
                            <x-dropdown-item href="/admin/posts" :active="request()->is('admin/posts')">
                                Dashboard
                            </x-dropdown-item>

                            <x-dropdown-item href="/admin/posts/create" :active="request()->is('admin/posts/create')">
                                New Post
                            </x-dropdown-item>
                        @endadmin

                        <x-dropdown-item href="#" x-data="{}"
                            @click.prevent="document.querySelector('#logout-form').submit()">
                            Log Out
                        </x-dropdown-item>

                        <form id="logout-form" method="POST" action="/logout" class="hidden">
                            @csrf
                        </form>
                    </x-dropdown>
                @else
                    <a href="/register"
                        class="text-xs font-bold uppercase {{ request()->is('register') ? 'text-blue-500' : '' }}">
                        Register
                    </a>

                    <a href="/login"
                        class="ml-6 text-xs font-bold uppercase {{ request()->is('login') ? 'text-blue-500' : '' }}">
                        Log In
                    </a>
                @endauth

                <a href="#newsletter"
                    class="bg-blue-500 ml-3 rounded-full text-xs font-semibold text-white uppercase py-3 px-5">
                    Subscribe for Updates
                </a>
            </div>
        </nav>

        {{ $slot }}

        <footer id="newsletter"
            class="bg-gray-100 border border-black border-opacity-5 rounded-xl text-center py-16 px-10 mt-16">
            <img src="/images/lary-newsletter-icon.svg" alt="" class="mx-auto -mb-6" style="width: 145px;">

            <h5 class="text-3xl">Stay in touch with the latest posts</h5>
            <p class="text-sm mt-3">Promise to keep the inbox clean. No bugs.</p>

            <div class="mt-10">
                <div class="relative inline-block mx-auto lg:bg-gray-200 rounded-full">

                    <form method="POST" action="/newsletter" class="lg:flex text-sm">
                        @csrf

                        <div class="lg:py-3 lg:px-5 flex items-center">
                            <label for="email" class="hidden lg:inline-block">
                                <img src="/images/mailbox-icon.svg" alt="mailbox letter">
                            </label>

                            <div>
                                <input id="email" name="email" type="text" placeholder="Your email address"
                                    class="lg:bg-transparent py-2 lg:py-0 pl-4 focus-within:outline-none">

                                @error('email')
                                    <span class="text-xs text-red-500">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <button type="submit"
                            class="transition-colors duration-300 bg-blue-500 hover:bg-blue-600 mt-4 lg:mt-0 lg:ml-3 rounded-full text-xs font-semibold text-white uppercase py-3 px-8">
                            Subscribe
                        </button>
                    </form>
                </div>
            </div>
        </footer>
    </section>

    <x-flash />
</body>
