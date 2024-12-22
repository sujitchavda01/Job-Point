<?php
?>
<footer class="footer">
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <img src="images/website/footer_logo.png" alt="Job Point Logo" class="footer-logo">
                <p>Connecting Dreams to Opportunities</p>
            </div>
            <div class="col-md-3">
                <h5>Job Categories</h5>
                <ul>
                    <li><a href="#">Engineering</a></li>
                    <li><a href="#">Programming</a></li>
                    <li><a href="#">Designing</a></li>
                    <li><a href="#">Consultants</a></li>
                    <li><a href="#">Agriculture</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Job Location</h5>
                <ul>
                    <li><a href="#">Ahmedabad</a></li>
                    <li><a href="#">Bangalore</a></li>
                    <li><a href="#">Delhi</a></li>
                    <li><a href="#">Hyderabad</a></li>
                    <li><a href="#">Indore</a></li>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Site Links</h5>
                <ul>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">About Us</a></li>
                    <li><a href="#">Contact Us</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Services</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>


<div class="copyright footerframe">
    <div class="container">
        <div class="row align-items-center justify-content-center"> 
            <div class="col-md-4 mt-2 col-sm-12 text-center d-flex flex-column align-items-center"> 
                <p class="mb-0 me-2">2024 © Copyrights</p>
            </div>
            <div class="col-md-4 col-sm-12 text-center d-flex align-items-center">
                <p class="mb-0 me-2 mt-2">Best of Luck❤️</p>
            </div>
            <div class="col-md-4 col-sm-12 text-center">
                <div class="social-icons d-flex align-items-center mt-1">
                    <a href="#" target="_blank"><i class="fab fa-google"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-twitter"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>






<?php
    if (isset($_SESSION['status']) && $_SESSION['status'] != '') {
        ?>
        <script>
            swal({
                title: "<?php echo $_SESSION['status_title']; ?>",
                text: "<?php echo $_SESSION['status']; ?>",
                icon: "<?php echo $_SESSION['status_code']; ?>",
                button: "OK",
            });
            
        </script>
        <?php
        unset($_SESSION['status_title']);
        unset($_SESSION['status']);
        unset($_SESSION['status_code']);
    }
    ?>


<script src="js/script.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
