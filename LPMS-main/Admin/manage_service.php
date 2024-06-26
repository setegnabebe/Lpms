
<div class="my-3 border shadow p-3" data-aos="fade-down-right">
    <div class="alert-outine-primary text-center my-2">
        <h3>Test SMS</h3>
    </div>
    <form method="POST" action="allphp.php">
        <div class="form-floating mb-3">
            <input type="text" class="form-control rounded-4" id="test-content" name='test-content' required>
            <label for="test-content">Message</label>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon1">+251</span>
            </div>
            <input type="text" name="tel" id="tel" class="form-control rounded-4" onkeypress="return onlyNumberKey(event)" placeholder="993819775" required pattern="[9]{1}[0-9]{8}" maxlength="9"required/>
        </div>
        <small class="text-secondary">Format : +251993819775</small>
        <div class="text-center alert">
            <button type='button' title='test-sms' onclick="prompt_confirmation(this)" class='btn btn-outline-primary mx-auto' name="test-sms">Send Message</button>
        </div>
    </form>
</div>

<div class="my-3 border shadow p-3" data-aos="fade-down-left">
    <div class="alert-outine-primary text-center my-2">
        <h3>Test Email</h3>
    </div>
    <form method="POST" action="allphp.php">
        <div class="form-floating mb-3">
            <input type="text" class="form-control rounded-4" id="test-content" name='test-content' required>
            <label for="test-content">Message</label>
        </div>
        <div class="form-floating mb-3">
            <input type="email" class="form-control rounded-4" id="email" name='email' required>
            <label for="email">Email</label>
        </div>
        <div class="text-center alert">
            <button type='button' title='test-email' onclick="prompt_confirmation(this)" class='btn btn-outline-primary mx-auto' name="test-email">Send Message</button>
        </div>
    </form>
</div>

<div class="my-3 border shadow p-3">
<h3 class="text-center my-3">Manage Announcements</h3>
    <div data-aos="fade-right">
        <form method="GET" action="allphp.php">
                <h4 class="modal-title text-light">Add Announcements</h4>
                <div class="form-floating mb-3">
                    <textarea class="form-control rounded-4" placeholder="Add an announcement here" id="Announcement" name='announcement' style="height: 100px"></textarea>
                    <label for="Announcement">Announcements</label>
                </div>
                <button class="btn btn-primary" type="submit" name="addAnnouncement">Add Announcement <i class="far fa-arrow-alt-circle-right fa-fw"></i></button>
        </form>
    </div>
    <div class='mx-auto' data-aos='fade-left'>
        <!-- <h3 class="text-center my-2">Manage Projects</h3> -->
        <form method="POST" action="allphp.php" class="mx-auto border shadow">
            <table class="table table-striped mt-3" id="table2">
                <thead class="table-primary">
                    <tr>
                        <th>Project</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $sql = "SELECT * FROM announcement ORDER BY id desc";
                    $stmt_announcement_all = $conn_mrf->prepare($sql); 
                    $stmt_announcement_all->execute();
                    $result = $stmt_announcement_all->get_result();
                    if($result->num_rows>0)
                        while($row = $result->fetch_assoc())
                        {
                            $ch =($row['status'] == 'active')?" checked":"";
                            echo "
                            <tr id='row_".$row['id']."'>
                                <td class='text-capitalize' id='id_".$row['id']."'>".$row['announcement']."</td>
                                <td>
                                    <div class='form-check form-switch'>
                                    <span class='text-capitalize' id='announcement_".$row['id']."'>".$row['status']." </span><input id='announcement_".$row['id']."' class='form-check-input' onchange='announcementChange(this)' type='checkbox' role='switch' id='statuss'$ch>
                                    </div>
                                </td>
                            </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </form>
    </div>
</div>