<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <title>About</title>
</head>
<body>
<img src="/img/FSF_Logo.png" alt="FSF Logo" style="width:120px; display:block; margin:auto;">

<!--<h1 style="text-align: center;"> Flow and Sensor Forecast</h1>-->

<p style=" text-align: center;">
    Department of Computer Science & Engineering<br>
    College of Engineering, University of Nevada, Reno<br>
    CS 426 Senior Projects in Computer Science<br>
</p>
<hr>
<h2 style="text-align:center;">Group 34</h2>
<p style ="text-align:center;"> Cory Bateman<br>
    Timothy Hand<br>
    Bohdi Norvell
</p>

<h2 style="text-align:center;">Instructors & TAs</h2>
<p style="text-align:center;">
    Dave Feil-Seifer<br>
    Vinh Le<br>
    Stosh Peterson<br>
    Richie White
</p>

<h2 style="text-align:center;">External Advisors</h2>
<p style="text-align:center">
    Austin Martin (Process Control Engineer, TMWRF)
</p>
<hr>
<h2 style="text-align:center;">Project Description</h2>
<p>
    Flow & Sensor Forecast (FSF) is a predictive analytics tool designed to support process
    control engineers at the Truckee Meadows Water Reclamation Facility. The system combines
    plant data, river gauge measurements, and local weather information to generate influent
    flow forecasts that help operators prepare for changing conditions. In addition to
    forecasting, FSF provides soft-sensor estimates for important plant measurements when
    physical sensors fail, drift, or are offline for maintenance. This gives engineers a
    reliable backup source of information and reduces uncertainty during unexpected equipment
    issues.
</p>
<p>
    The main goal of FSF is to give engineers a straightforward platform for uploading and
    using predictive models as soft sensors. These models are trained on historical plant data
    and environmental conditions so they can mimic the behavior of real sensors. When a
    physical sensor becomes unavailable, the model can provide an estimated value that helps
    operators maintain awareness of plant conditions and continue making informed decisions.
    This reduces the risks that come with missing or unreliable sensor data and supports
    smoother day-to-day operations at the facility.
</p>


<p>
    FSF is built using Laravel, Vue, and Python, combining web development and machine learning
    in a single system.Overall, FSF aims to provide a practical and lightweight solution
    that improves reliability, supports decision-making, and gives engineers a flexible way
    to deploy and manage predictive models as part of their daily workflow.
</p>
<hr>
<h2 style="text-align:center;">Project Resources</h2>

<p>
    <strong>Problem Domain Book:</strong><br>
    <a href="https://www.mheducation.com/highered/product/wastewater-engineering-treatment-resource-recovery-tchobanoglous-stensel/M9780073401188.html" target="_blank">
        Wastewater Engineering: Treatment and Resource Recovery (Metcalf & Eddy)
    </a>
</p>

<p>
    <strong>Useful Websites:</strong><br>
    <a href="https://waterdata.usgs.gov/nwis" target="_blank">USGS National Water Information System</a><br>
    <a href="https://www.weather.gov" target="_blank">NOAA National Weather Service</a>
</p>

<p>
    <strong>Technical References:</strong><br>
    <a href="https://www.sciencedirect.com/topics/engineering/soft-sensor" target="_blank">Soft Sensors in Wastewater Treatment</a><br>
    <a href="https://www.epa.gov/waterdata/water-quality-data-wqx" target="_blank">EPA Water Quality Exchange (WQX)</a>
</p>



<button onclick="window.history.back()">Go Back</button>
</body>
