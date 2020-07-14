<div class="row">
    <div class="col-lg-12">       
        <p>Please check /restful/Authorization/authorization.php</p>
        <?php if(empty($authorizationFile)): ?>
            <div class="alert alert-danger" role="alert">
            <?= $promptAuthorizationEmpty ?>
            </div>
        <?php endif; ?>
        <form method="POST" action="<?= $baseUrl.$url->segment(1).'/authorization_util_save' ?>">
            <?php 
                $data = null;
                $isChecked = null;
                $data .= '<ul>';
                if (!empty($userRoles)) {
                    foreach($userRoles as $role) {
                        $data .= '<li>';
                        $data .= '<strong>'. $role .'</strong>';
                        if (!empty($endpoints)) {
                            $ctr = 0;
                            foreach($endpoints as $endpoint) {
                                $checkboxName = 'checkbox-'.$role.'-'.$ctr++;
                                
                                if (!empty($authorizationFile[$role])) {                                        
                                    $isChecked = (in_array($endpoint, $authorizationFile[$role])) ? "checked ":"";
                                }

                                $data .= '<div class="form-check">
                                                <input '.$isChecked.' class="form-check-input" type="checkbox" name="'.$role.'[]" value="'.$endpoint.'" id="'.$checkboxName.'" >
                                                <label class="form-check-label" for="'.$checkboxName.'">
                                                Can access '.$endpoint.'? 
                                                </label>
                                            </div>';
                                $isChecked = null;
                            }
                        }
                        $data .= '</li>';

                    }
                }
                $data .= '</ul>';  
                echo $data;
            ?>
            <button name="submit" type="submit" class="btn btn-primary btn-block" style="width: 40%">Save</button>
        </form>
    </div>
</div>
