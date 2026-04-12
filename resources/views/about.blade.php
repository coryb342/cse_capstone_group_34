<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About – Flow & Sensor Forecast</title>
    @vite(['resources/js/app.ts'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'DM Sans', sans-serif; }
        code, .mono { font-family: 'DM Mono', monospace; }

        @keyframes wave {
            0%   { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .wave-anim { animation: wave 12s linear infinite; }

        @media (prefers-color-scheme: light) {
            .logo-img { mix-blend-mode: multiply; }
            .hero-bg {
                background: linear-gradient(160deg, #eff6ff 0%, #dbeafe 40%, #e0f2fe 100%);
            }
        }
        @media (prefers-color-scheme: dark) {
            .logo-img { filter: brightness(1.6) saturate(1.3) drop-shadow(0 0 16px rgba(56,189,248,0.6)); }
            .hero-bg {
                background: linear-gradient(160deg, #020b18 0%, #071d36 40%, #04111f 100%);
            }
        }

        .tag-chip {
            font-family: 'DM Mono', monospace;
            font-size: 0.7rem;
        }

        /* screenshot */
        @media (prefers-color-scheme: light) {
            .ss-bg { background: linear-gradient(145deg, #dbeafe, #bae6fd, #cffafe); }
            .ss-inner { background: white; border: 1px solid #e0f2fe; }
            .ss-dot { background: #93c5fd; }
        }
        @media (prefers-color-scheme: dark) {
            .ss-bg { background: linear-gradient(145deg, #051525, #0a2540, #071c2e); }
            .ss-inner { background: #0c1e30; border: 1px solid #0f3460; }
            .ss-dot { background: #1e4a7a; }
        }
    </style>
</head>
<body class="bg-white dark:bg-black min-h-screen text-gray-900 dark:text-gray-100">

<div class="hero-bg">
    <div class="max-w-5xl mx-auto px-6 pt-16 pb-20">
        <div class="flex flex-col md:flex-row items-center gap-10">

            <div class="flex-shrink-0">
                <img src="/img/FSF_Logo.png" alt="FSF Logo" class="logo-img w-36 h-36 object-contain">
            </div>

            <div>
                <div class="flex flex-wrap gap-2 mb-5">
                    <span class="tag-chip bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-sky-400 px-3 py-1 rounded-md">Laravel</span>
                    <span class="tag-chip bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-sky-400 px-3 py-1 rounded-md">Vue</span>
                    <span class="tag-chip bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-sky-400 px-3 py-1 rounded-md">Python</span>
                    <span class="tag-chip bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-sky-400 px-3 py-1 rounded-md">Docker</span>
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-blue-950 dark:text-white leading-tight mb-3">
                    Flow &amp; Sensor<br>Forecast
                </h1>
                <p class="text-gray-500 dark:text-gray-400 text-sm leading-relaxed">
                    Department of Computer Science &amp; Engineering ·
                    College of Engineering, University of Nevada, Reno ·
                    CS 426 Senior Projects in Computer Science
                </p>
            </div>

        </div>
    </div>
</div>

<div class="overflow-hidden h-10 relative bg-white dark:bg-black -mt-1">
    <svg class="wave-anim absolute top-0 left-0 w-[200%]" viewBox="0 0 1440 40" preserveAspectRatio="none" height="40">
        <path d="M0,20 C240,40 480,0 720,20 C960,40 1200,0 1440,20 L1440,0 L0,0 Z"
              fill="none" stroke="#0ea5e9" stroke-width="1.5" opacity="0.3"/>
    </svg>
</div>

<div class="max-w-5xl mx-auto px-6 py-12">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-16">

        <div class="rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <span class="mono text-xs text-blue-500 dark:text-sky-500 font-medium">Group 34</span>
            </div>
            <ul class="space-y-1 text-gray-700 dark:text-gray-300 text-sm font-medium">
                <li>Cory Bateman</li>
                <li>Timothy Hand</li>
                <li>Bohdi Norvell</li>
            </ul>
        </div>

        <div class="rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-2 rounded-full bg-sky-400"></div>
                <span class="mono text-xs text-blue-500 dark:text-sky-500 font-medium">Instructors &amp; TAs</span>
            </div>
            <ul class="space-y-1 text-gray-700 dark:text-gray-300 text-sm font-medium">
                <li>Dave Feil-Seifer</li>
                <li>Vinh Le</li>
                <li>Stosh Peterson</li>
                <li>Richie White</li>
            </ul>
        </div>

        <div class="rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-900 p-6">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-2 h-2 rounded-full bg-cyan-400"></div>
                <span class="mono text-xs text-blue-500 dark:text-sky-500 font-medium">External Advisors</span>
            </div>
            <div class="text-gray-700 dark:text-gray-300 text-sm font-medium">
                <p>Austin Martin</p>
                <p class="text-gray-400 dark:text-gray-500 text-xs mt-1 font-normal">Process Control Engineer, TMWRF</p>
            </div>
        </div>

    </div>

    <section class="mb-16">
        <div class="flex items-center gap-4 mb-8">
            <h2 class="text-2xl font-bold text-blue-950 dark:text-white">Project Description</h2>
            <div class="flex-1 h-px bg-gradient-to-r from-blue-200 dark:from-blue-900 to-transparent"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-x-10 gap-y-5 text-gray-600 dark:text-gray-300 text-sm leading-relaxed">
            <p>
                Flow &amp; Sensor Forecast (FSF) is a predictive analytics tool designed to support process
                control engineers at the Truckee Meadows Water Reclamation Facility. As part of the project we combined
                plant data, river gauge measurements, and local weather information to train predictive models for influent
                flow, and manhole level sensors. These models can then be uploaded to our application and used in various ways.
            </p>
            <p>
                In addition to making one time predictions, FSF provides soft-sensor implementation for important plant measurements when
                physical sensors fail, drift, or are offline for maintenance. This gives engineers a
                reliable backup source of information and reduces uncertainty during unexpected equipment issues by allowing users to combine live
                datastreams with uploaded models to get interval based predictions and real time comparisons of actual and predicted values.
            </p>
            <p>
                The main goal of FSF is to give engineers a straightforward platform for uploading and
                using predictive models as soft sensors. These models are trained on historical plant data
                and environmental conditions so they can mimic the behavior of real sensors.
            </p>
            <p>
                FSF is built using <strong class="text-blue-700 dark:text-sky-300 font-semibold">Laravel</strong>, <strong class="text-blue-700 dark:text-sky-300 font-semibold">Vue</strong>, and <strong class="text-blue-700 dark:text-sky-300 font-semibold">Python</strong>, combining web development and machine learning
                in a single system. FSF aims to provide a practical and lightweight solution
                that improves reliability and gives engineers a flexible way to deploy predictive models.
            </p>
        </div>
    </section>

    <section class="">
        <div class="flex items-center gap-4 mb-8">
            <h2 class="text-2xl font-bold text-blue-950 dark:text-white">App Screenshots</h2>
            <div class="flex-1 h-px bg-gradient-to-r from-blue-200 dark:from-blue-900 to-transparent"></div>
        </div>

        <div class="flex gap-5 overflow-x-auto md:grid md:grid-cols-3 md:overflow-visible">

            <div class="flex-shrink-0 w-72 md:w-auto rounded-xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-lg dark:hover:shadow-sky-950 transition-all duration-200 hover:-translate-y-1">
                <div class="h-40 overflow-hidden bg-gray-50 dark:bg-gray-900">
                    <img src="/img/app_screenshots/models_dark.png"
                         alt="Flow and Sensor Forecast Models Page screenshot"
                         class="w-full h-full"/>
                </div>

                <div class="p-3 bg-white dark:bg-gray-950">
                    <p class="mono text-xs text-gray-500 dark:text-gray-500">01 /Models</p>
                </div>
            </div>

            <div class="flex-shrink-0 w-72 md:w-auto rounded-xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-lg dark:hover:shadow-sky-950 transition-all duration-200 hover:-translate-y-1">
                <div class="h-40 overflow-hidden bg-gray-50 dark:bg-gray-900">
                    <img src="/img/app_screenshots/model_show_dark.png"
                         alt="Flow and Sensor Forecast Models Show Page screenshot"
                         class="w-full h-full"/>
                </div>

                <div class="p-3 bg-white dark:bg-gray-950">
                    <p class="mono text-xs text-gray-500 dark:text-gray-500">02 /Models/id</p>
                </div>
            </div>

            <div class="flex-shrink-0 w-72 md:w-auto rounded-xl overflow-hidden border border-gray-100 dark:border-gray-800 shadow-sm hover:shadow-lg dark:hover:shadow-sky-950 transition-all duration-200 hover:-translate-y-1">
                <div class="h-40 overflow-hidden bg-gray-50 dark:bg-gray-900">
                    <img src="/img/app_screenshots/soft_sensors_dark.png"
                         alt="Flow and Sensor Forecast Soft Sensors Page screenshot"
                         class="w-full h-full"/>
                </div>

                <div class="p-3 bg-white dark:bg-gray-950">
                    <p class="mono text-xs text-gray-500 dark:text-gray-500">03 /Soft-Sensors</p>
                </div>
            </div>

        </div>
    </section>

    <div class="h-px bg-gray-100 dark:bg-gray-900 mb-14"></div>

    <section class="mb-16">
        <div class="flex items-center gap-4 mb-8">
            <h2 class="text-2xl font-bold text-blue-950 dark:text-white">Project Resources</h2>
            <div class="flex-1 h-px bg-gradient-to-r from-blue-200 dark:from-blue-900 to-transparent"></div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 text-sm">
            <div>
                <p class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Problem Domain Book</p>
                <a href="https://www.mheducation.com/highered/product/wastewater-engineering-treatment-resource-recovery-tchobanoglous-stensel/M9780073401188.html"
                   target="_blank"
                   class="text-blue-600 dark:text-sky-400 hover:underline leading-relaxed">
                    Wastewater Engineering: Treatment and Resource Recovery (Metcalf &amp; Eddy)
                </a>
            </div>
            <div>
                <p class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Useful Websites</p>
                <div class="space-y-1.5">
                    <div><a href="https://waterdata.usgs.gov/nwis" target="_blank" class="text-blue-600 dark:text-sky-400 hover:underline">USGS National Water Information System</a></div>
                    <div><a href="https://www.weather.gov" target="_blank" class="text-blue-600 dark:text-sky-400 hover:underline">NOAA National Weather Service</a></div>
                </div>
            </div>
            <div>
                <p class="font-semibold text-gray-900 dark:text-gray-100 mb-2">Technical References</p>
                <div class="space-y-1.5">
                    <div><a href="https://www.sciencedirect.com/topics/engineering/soft-sensor" target="_blank" class="text-blue-600 dark:text-sky-400 hover:underline">Soft Sensors in Wastewater Treatment</a></div>
                    <div><a href="https://www.epa.gov/waterdata/water-quality-data-wqx" target="_blank" class="text-blue-600 dark:text-sky-400 hover:underline">EPA Water Quality Exchange (WQX)</a></div>
                </div>
            </div>
        </div>
    </section>

    <div class="pb-16 flex justify-start">
        <button
                class="inline-flex items-center gap-2 text-sm font-medium px-4 py-2 rounded-xl bg-blue-950 dark:bg-sky-500 text-white dark:text-black hover:bg-blue-800 dark:hover:bg-sky-400 transition-colors">
            <a href="{{ route('home') }}">Home</a>
        </button>
    </div>

</div>
</body>
</html>
