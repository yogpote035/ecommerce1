<?php
require_once 'init.php';
$siteTitle = 'Contact Us';
include 'templates/header.php';
?>
<section class="pt-1 pb-5">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-6">
        <h1 class="display-4 font-weight-bold">Contact Retail Pvt Ltd.</h1>
        <p class="lead text-muted">Reach out for support, sales, or product information.</p>
      </div>
    </div>

    <style>
      .contact-equal-height-row {
        align-items: stretch;
      }
      .contact-card-fullheight {
        height: 100%;
      }
      .contact-map-wrapper {
        position: relative;
        width: 100%;
        height: 100%;
      }
      .contact-map-wrapper iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
      }
    </style>

    <div class="row mt-5 contact-equal-height-row">
      <div class="col-lg-6 mb-4 d-flex">
        <div class="card border-0 shadow-sm rounded-lg p-4 h-100 w-100">
          <div class="card-body d-flex flex-column">
            <h2 class="h4 font-weight-bold mb-3">Customer Support</h2>
            <p class="text-muted mb-4">Our team is ready to help with orders, returns, and general questions.</p>
            <ul class="list-unstyled mb-4 flex-grow-1">
              <li class="mb-3">
                <h6 class="mb-1">Address</h6>
                <p class="mb-0">WZ-622, St-9, Ramgarh Colony, New Delhi, Delhi 110015</p>
              </li>
              <li class="mb-3">
                <h6 class="mb-1">Customer Care</h6>
                <p class="mb-0"><a href="tel:7982909087">7982909087</a></p>
              </li>
              <li>
                <h6 class="mb-1">Email</h6>
                <p class="mb-0"><a href="mailto:retail@sales.in">retail@sales.in</a></p>
              </li>
            </ul>
            <a href="Home.php" class="btn btn-primary mt-auto">Back to Home</a>
          </div>
        </div>
      </div>
      <div class="col-lg-6 d-flex">
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden h-100 w-100">
          <div class="contact-map-wrapper">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3501.1253867389587!2d77.1323627149239!3d28.655964082408477!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d0309287df3c5%3A0xb8b7a2787e88aa69!2sLane%20Number%209%2C%20Ram%20Garh%20Colony%2C%20Basai%20Dara%20pur%2C%20Bali%20Nagar%2C%20New%20Delhi%2C%20Delhi%20110015!5e0!3m2!1sen!2sin!4v1617719678200!5m2!1sen!2sin" frameborder="0" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include 'templates/footer.php';
