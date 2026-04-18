<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organiser Dashboard</title>
    <link rel="stylesheet" href="../Organiser-css/dashboard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
</head>
<body>
    <div class="sidebar">
        <div class="logo"></div>
        <ul class="menu">
            <li class="active">
                <a href="#" >
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="#">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </li>
            <li class="logout">
                <a href="#" >
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <div class="main--content">
        <div class="header--wrapper">
            <div class="header--title">
                <span>Primary</span>
                <h2>Organiser Dashboard</h2>
            </div>
            <div class="user--info">
                <div class="search--box">
                <i class="fa-solid fa-search">
                    <input type="text" placeholder="Search" />
                </i>
                </div>
                <img src="../images/profile.avif" alt="" />
            </div>
        </div>
    
    <div class="card--container">
        <h3 class="main--title">My Competition</h1>
        <div class="card--wrapper">
            <div class="competition--card light-red">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Total Competition
                        </span>
                        <span class="amount--value">5</span>
                    </div>
                    <i class="fas fa-trophy icon"></i>
                </div>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Published
                        </span>
                        <span class="amount--value">2</span>
                    </div>
                    <i class="fa-solid fa-circle-check icon dark-purple"></i>
                </div>
            </div>

            <div class="competition--card light-blue">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Pending Review
                        </span>
                        <span class="amount--value">1</span>
                    </div>
                    <i class="fa-solid fa-clock icon dark-blue"></i>
                </div>
            </div>

            <div class="competition--card light-purple">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Total Participants
                        </span>
                        <span class="amount--value">2</span>
                    </div>
                    <i class="fa-solid fa-user-group icon dark-purple"></i>
                </div>
            </div>

            <div class="competition--card light-red">
                <div class="card--header">
                    <div class="amount">
                        <span class="title">
                            Total Submission
                        </span>
                        <span class="amount--value">5</span>
                    </div>
                    <i class="fa-solid fa-file icon"></i>
                </div>
            </div>

        </div>
    </div>
        <div class="card--container">
            <div class="table--container">
                <h3 class="main--title">Competition List</h3>

                <table>
                    <thead>
                        <tr>
                            <th>Competition</th>
                            <th>Status</th>
                            <th>Participants</th>
                            <th>Submissions</th>
                            <th>Deadline</th>
                            <th>Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td>Global Hackathon 2025</td>
                            <td><span class="status published">Published</span></td>
                            <td>1,243</td>
                            <td>856</td>
                            <td>Feb 20, 2026</td>
                            <td>
                                <i class="fa fa-eye action-icon"></i>
                                <i class="fa fa-edit action-icon"></i>
                            </td>
                        </tr>

                        <tr>
                            <td>Creative Design Challenge</td>
                            <td><span class="status approved">Approved</span></td>
                            <td>567</td>
                            <td>423</td>
                            <td>Jan 31, 2026</td>
                            <td>
                                <i class="fa fa-eye action-icon"></i>
                                <i class="fa fa-edit action-icon"></i>
                            </td>
                        </tr>

                        <tr>
                            <td>Innovation Tech Summit</td>
                            <td><span class="status pending">Pending</span></td>
                            <td>0</td>
                            <td>0</td>
                            <td>Mar 15, 2026</td>
                            <td>
                                <i class="fa fa-eye action-icon"></i>
                                <i class="fa fa-edit action-icon"></i>
                            </td>
                        </tr>

                        <tr>
                            <td>Business Pitch Competition</td>
                            <td><span class="status rejected">Rejected</span></td>
                            <td>0</td>
                            <td>0</td>
                            <td>Mar 10, 2026</td>
                            <td>
                                <i class="fa fa-eye action-icon"></i>
                                <i class="fa fa-edit action-icon"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    

    </div>


</body>
</html>