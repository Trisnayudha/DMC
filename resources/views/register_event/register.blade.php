<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        @media screen and (min-width: 1000px) {
            .custom-card {
                margin-top: 50px;
            }

        }

        @media screen and (max-width: 1000px) {
            .bebas {
                flex: 0 0 auto !important;
                width: 100% !important;
            }
        }

        ul.timeline {
            list-style: none;
            padding: 0;
        }

        ul.timeline>li {
            position: relative;
            margin-left: 20px;
            margin-bottom: 20px;
            padding-left: 20px;
            border-left: 2px solid #ccc;
        }

        ul.timeline>li .timeline-badge {
            position: absolute;
            top: 0;
            left: -10px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #fff;
            text-align: center;
            line-height: 20px;
        }

        ul.timeline>li .timeline-panel {
            margin-right: 10px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            /* Tambahkan efek bayangan */
        }

        ul.timeline>li .speaker img {
            max-width: 100px;
            border-radius: 50%;
            margin-top: 10px;
        }

        ul.timeline>li .speaker p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="{{ asset('image/dmc.png') }}" alt="Bootstrap" width="100" height="40">
            </a>
            <ul class="nav nav-pills justify-content-end">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="#">REGISTER EVENT</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">FAQ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link disabled" href="#">CONTACT US</a>
                </li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <div class="row justify-content-md-center ">
            <div class="col-12 col-md-8">
                <img src="https://usercontent.hopin.com/events/pictures/000/310/033/original/1IB.jpeg?1642007418"
                    alt="" class="img-fluid">
            </div>
            <div class="col-12 col-md-4">
                <div class="card custom-card">
                    <div class="card-body d-flex flex-column justify-content-between">
                        <!-- Added classes "d-flex flex-column justify-content-between" -->
                        <div>
                            <h2 class="card-title">Special title treatment</h2>
                            <p class="card-text">The live event is over.</p>
                        </div>
                        <div class="row">
                            <div class="col">
                                <h6>Start Time </h6>
                                <p>31 August, 14:00 PM</p>
                            </div>
                            <div class="col">
                                <h6>End Time </h6>
                                <p>31 August, 19:00 AM</p>
                            </div>
                        </div>
                        <a href="{{ url('register/step') }}" class="btn btn-primary">Register Event</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 col-md-8">
                        <ul class="nav nav-tabs" id="myTab" role="tablist"
                            style="position: sticky; top:0px; background: white">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="home-tab" data-bs-toggle="tab"
                                    data-bs-target="#home-tab-pane" type="button" role="tab"
                                    aria-controls="home-tab-pane" aria-selected="true">Description</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="profile-tab" data-bs-toggle="tab"
                                    data-bs-target="#profile-tab-pane" type="button" role="tab"
                                    aria-controls="profile-tab-pane" aria-selected="false">Schedule</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="contact-tab" data-bs-toggle="tab"
                                    data-bs-target="#contact-tab-pane" type="button" role="tab"
                                    aria-controls="contact-tab-pane" aria-selected="false">Speakers</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="myTabContent">

                            {{-- DESCRIPTION --}}
                            <div class="tab-pane fade show active" id="home-tab-pane" role="tabpanel"
                                aria-labelledby="home-tab" tabindex="0">
                                <p style="text-align: justify">
                                    Lorem ipsum dolor sit amet consectetur adipisicing elit. Facilis velit quos, neque
                                    inventore quia praesentium a expedita saepe quibusdam temporibus, debitis eos? Cum
                                    quibusdam laudantium officia ab deserunt, dignissimos repellat.
                                    Veritatis aspernatur voluptate modi provident reprehenderit, atque necessitatibus
                                    dolor illum enim in iure perferendis debitis doloremque minus tenetur fugiat maiores
                                    soluta iusto obcaecati neque magni facilis, harum tempore iste? Impedit!
                                    Facere quo corporis architecto perferendis in repudiandae tempora nesciunt veritatis
                                    amet omnis, unde error excepturi molestiae cupiditate fuga dolorum incidunt quia
                                    odio qui. In, praesentium aperiam similique dolores amet architecto.
                                    Accusamus ullam doloribus ratione quisquam iusto distinctio sed minima deserunt
                                    doloremque reprehenderit ipsa eum, architecto amet voluptatem commodi error
                                    cupiditate ea ut voluptatum eos. Ratione odio excepturi reprehenderit ducimus
                                    quibusdam?
                                    Facere, dignissimos iure nam culpa consequatur dolor quod ab vel sint inventore
                                    molestiae molestias iste, blanditiis amet cum aspernatur voluptate, quisquam
                                    expedita officia error sapiente. Animi soluta ipsam esse nulla?
                                    Magnam quasi animi maiores aliquid voluptates soluta dignissimos sapiente, dolor
                                    corrupti recusandae illum asperiores possimus sint qui earum quaerat quae. Nemo unde
                                    quae, dicta vitae nesciunt laudantium expedita ipsa minima!
                                    Debitis harum ut, expedita similique voluptatum tenetur placeat repellat quidem odio
                                    unde ratione minima quam et! Et non necessitatibus obcaecati fugiat quas, autem,
                                    accusamus libero nihil provident sequi incidunt odit!
                                    Quo quibusdam voluptatum magnam quas praesentium commodi iusto, similique cum
                                    explicabo, tempora eligendi at fuga itaque, ipsam consequuntur voluptas eaque
                                    tempore deleniti blanditiis repellat ipsum quasi delectus alias? Architecto, et!
                                    Illo corrupti hic velit nihil minus dolorum! Ipsa sunt explicabo in similique
                                    eligendi, neque nostrum repellendus minus. Ducimus vitae, sed, in debitis quibusdam
                                    labore molestiae soluta quam eos architecto facilis.
                                    Eum, culpa non maiores quisquam, neque ab ex similique facilis cupiditate quasi
                                    recusandae quod. Minima ullam quidem quisquam, saepe temporibus laboriosam, suscipit
                                    quae, fugit necessitatibus numquam cumque facilis quam alias.
                                    Numquam, nostrum dolores tempora blanditiis dicta id reiciendis ex magni obcaecati
                                    optio iusto eum voluptas quae eveniet facilis? Laudantium excepturi dolores
                                    laboriosam quia tempore cupiditate voluptatibus laborum, dignissimos sed corrupti.
                                    Exercitationem aliquam veritatis quos necessitatibus porro, voluptate temporibus,
                                    quam delectus vero mollitia omnis dignissimos officiis doloribus dolor reiciendis
                                    maxime soluta, sunt a autem deserunt! Sunt nihil et totam asperiores! Perferendis.
                                    In dolores repudiandae corrupti libero accusantium esse deleniti natus distinctio,
                                    repellendus nesciunt numquam dolorum quam suscipit maxime hic corporis
                                    necessitatibus harum voluptas pariatur cum error asperiores porro. Ex, illo at!
                                    Odit impedit atque consequatur deleniti perspiciatis voluptate, fugiat earum facere
                                    debitis culpa pariatur ab labore accusamus blanditiis facilis quibusdam expedita ea
                                    vel nobis sed reiciendis aliquam dolorem repellendus neque! Et!
                                    Qui, ex velit eaque tempore accusantium nam temporibus distinctio ratione harum!
                                    Maxime blanditiis exercitationem veniam accusamus placeat ipsum praesentium earum
                                    veritatis ex. Velit earum tenetur dolores soluta distinctio, recusandae omnis!
                                    Commodi repellat illum perspiciatis doloremque hic, expedita vero! Quibusdam
                                    consectetur repudiandae eveniet incidunt. Laboriosam excepturi est iusto hic ducimus
                                    quis qui placeat odit. Modi, aut dolore nam suscipit necessitatibus deserunt?
                                    Commodi quasi unde autem rerum aut accusantium itaque, corrupti distinctio eos, quos
                                    aspernatur beatae atque necessitatibus ut vero. Possimus impedit unde quis molestias
                                    accusantium earum maxime! Veniam aspernatur sapiente in.
                                    Debitis ea culpa nam tenetur autem commodi totam, qui neque accusantium, quos unde
                                    doloremque. Velit ut, possimus quos suscipit animi beatae molestiae natus
                                    consectetur corporis aspernatur dolorem quis, minus deleniti!
                                    Vero delectus dignissimos voluptates praesentium suscipit iste quas maxime quos nisi
                                    nesciunt! Quaerat, quae ducimus alias magnam sint dolorum tenetur praesentium
                                    suscipit animi modi nostrum odit rem id voluptas quo?
                                    Eaque quidem debitis quae accusantium ipsum suscipit. Qui nostrum numquam odio quos,
                                    eius tempora quidem ea facilis sit rerum, voluptatem architecto nobis dolorum,
                                    molestias eveniet repellat saepe facere distinctio repudiandae!
                                </p>

                            </div>

                            {{-- SCHEDULE --}}
                            <div class="tab-pane fade" id="profile-tab-pane" role="tabpanel"
                                aria-labelledby="profile-tab" tabindex="0">
                                <ul class="timeline mt-2">
                                    <!-- Item 1 -->
                                    <li>
                                        <div class="timeline-badge"><i class="fas fa-clock"></i> </div>
                                        <div class="timeline-panel">
                                            <div class="timeline-heading">
                                                <h4 class="timeline-title">Opening Ceremony</h4>
                                                <p><small class="text-muted">10:00 AM</small></p>
                                            </div>
                                            <div class="timeline-body">
                                                <p>Location: Main Stage</p>
                                                <p>Join us for the official opening of the event.</p>
                                                <div class="speaker" style="display: flex">
                                                    <img src="https://via.placeholder.com/150" alt="Speaker">
                                                    <div style="margin-left: 10px">

                                                        <p>Name: John Doe</p>
                                                        <p>Job Title: CEO, Company XYZ</p>
                                                        <p>
                                                            <a href="https://www.linkedin.com/in/johndoe"
                                                                target="_blank"><i class="fab fa-linkedin"></i></a>
                                                            <a href="https://www.facebook.com/johndoe"
                                                                target="_blank"><i class="fab fa-facebook"></i></a>
                                                            <a href="https://www.twitter.com/johndoe"
                                                                target="_blank"><i class="fab fa-twitter"></i></a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <!-- Item 2 -->
                                    <li>
                                        <div class="timeline-badge"><i class="fas fa-map-marker-alt"></i> </div>
                                        <div class="timeline-panel">
                                            <div class="timeline-heading">
                                                <h4 class="timeline-title">Networking Session</h4>
                                                <p><small class="text-muted">11:00 AM</small></p>
                                            </div>
                                            <div class="timeline-body">
                                                <p>Location: Networking Area</p>
                                                <p>Connect with fellow attendees in this informal networking session.
                                                </p>
                                                <div class="speaker" style="display: flex">
                                                    <img src="https://via.placeholder.com/150" alt="Speaker">
                                                    <div style="margin-left: 10px">

                                                        <p>Name: Jane Smith</p>
                                                        <p>Job Title: CTO, Tech Solutions</p>
                                                        <p>
                                                            <a href="https://www.linkedin.com/in/janesmith"
                                                                target="_blank"><i class="fab fa-linkedin"></i></a>
                                                            <a href="https://www.facebook.com/janesmith"
                                                                target="_blank"><i class="fab fa-facebook"></i></a>
                                                            <a href="https://www.twitter.com/janesmith"
                                                                target="_blank"><i class="fab fa-twitter"></i></a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                    <!-- ... Continue adding more schedule items -->
                                </ul>
                            </div>

                            {{-- SPEAKER --}}
                            <div class="tab-pane fade" id="contact-tab-pane" role="tabpanel"
                                aria-labelledby="contact-tab" tabindex="0">
                                <div class="row mb-3">
                                    <div class="col-xs-12 col-sm-12 col-md-6 bebas">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="speaker" style="display: flex">
                                                    <img src="https://via.placeholder.com/150" alt="Speaker"
                                                        style="border-radius: 50%">
                                                    <div style="margin-left: 10px">

                                                        <p>Name: Jane Smith</p>
                                                        <p>Job Title: CTO, Tech Solutions</p>
                                                        <p>
                                                            <a href="https://www.linkedin.com/in/janesmith"
                                                                target="_blank"><i class="fab fa-linkedin"></i></a>
                                                            <a href="https://www.facebook.com/janesmith"
                                                                target="_blank"><i class="fab fa-facebook"></i></a>
                                                            <a href="https://www.twitter.com/janesmith"
                                                                target="_blank"><i class="fab fa-twitter"></i></a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-xs-12 col-sm-12 col-md-6 bebas">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="speaker" style="display: flex">
                                                    <img src="https://via.placeholder.com/150" alt="Speaker"
                                                        style="border-radius: 50%">
                                                    <div style="margin-left: 10px">

                                                        <p>Name: Jane Smith</p>
                                                        <p>Job Title: CTO, Tech Solutions</p>
                                                        <p>
                                                            <a href="https://www.linkedin.com/in/janesmith"
                                                                target="_blank"><i class="fab fa-linkedin"></i></a>
                                                            <a href="https://www.facebook.com/janesmith"
                                                                target="_blank"><i class="fab fa-facebook"></i></a>
                                                            <a href="https://www.twitter.com/janesmith"
                                                                target="_blank"><i class="fab fa-twitter"></i></a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>


                    <div class="col-12 col-md-4">
                        <div class="shadow-sm p-3 mb-5 bg-white rounded">
                            <div style="display: flex">
                                <img src="https://djakarta-miningclub.com/wp-content/uploads/2019/05/slid-rpm-global-e1558949231990.png"
                                    alt="" height="50">
                                <div>
                                    <p style="margin-bottom: 0px; margin-left:5px">Hosted by</p>

                                    <p style="margin-left:5px">RPM Global</p>
                                </div>
                            </div>
                            <h6>
                                Conferences for Business Development & Recruitments in the Pharmaceutical Industry.
                            </h6>
                            <div style="display: flex">
                                <a href="https://www.example.com" target="_blank" style="margin-right: 10px;">
                                    <i class="fas fa-globe"></i>
                                </a>
                                <a href="https://www.linkedin.com/in/yourprofile" target="_blank"
                                    style="margin-right: 10px;">
                                    <i class="fab fa-linkedin"></i>
                                </a>
                                <a href="mailto:youremail@example.com" style="margin-right: 10px;">
                                    <i class="far fa-envelope"></i>
                                </a>
                            </div>

                        </div>
                        <div>
                            <div class="text-center mb-3"><strong>Advertisement</strong></div>
                            <img src="https://indonesiaminer.com/uploads/2/2022-10/directory_homepage_1500_750_px.png"
                                alt="Advertisement" class="img-fluid mt-2">
                            <img src="https://indonesiaminer.com/uploads/2/2023-06/gra_0999_04_mining_industry_1920x1080_h.jpg"
                                alt="Advertisement" class="img-fluid mt-2">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous">
    </script>
</body>

</html>
