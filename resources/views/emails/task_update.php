
<?php if($task['type']==''){ ?>
<div>
                            <table width="90%" cellpadding="2" cellspacing="2">
                              <tbody>
                                  <tr>
                                      <td align="left">
                                          <h3>
                                              <?php echo strtoupper($task['title']); ?>
                                          </h3>
                                      </td>
                                  </tr>
                                  
                                <tr>
                                    <td align="left">
                                        Created By
                                    </td>
                                    <td align="left">
                                        <strong><?php echo $task['created_by'];?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                        Category
                                    </td>
                                    <td align="left">
                                        <strong><?php echo $task['category'];?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                        Task
                                    </td>
                                    <td align="left">
                                        <strong><?php echo $task['title'];?></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                       Begin On
                                    </td>
                                    <td align="left">
                                        <strong><span class="aBn" data-term="goog_496202186" tabindex="0"><span class="aQJ"><?php echo $task['begin_on'];?></span></span></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="left">
                                        Due On
                                    </td>
                                    <td align="left">
                                        <strong><span class="aBn" data-term="goog_496202187" tabindex="0"><span class="aQJ"><?php echo $task['due_on'];?></span></span></strong>
                                    </td>
                                </tr>
                              </tbody></table>
</div>
<?php } if($task['type']=='C'){ ?>
<p>
    <?php echo $task['title']; ?> assign to <?php echo $task['assign_to'];?>
</p>
<p>
    Closed by <?php echo $task['change_by'];?>
</p>

<?php } if($task['type']=='O') { ?>
        <p>
    <?php echo $task['title']; ?> assign to <?php echo $task['assign_to'];?>
</p>
<p>
    Re-open by <?php echo $task['change_by'];?>
</p>
<?php } ?>

