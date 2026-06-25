<script>
    document.querySelector('[data-login-form]').addEventListener('submit', function(event) {
        event.preventDefault(); // Stop standard form refresh
        
        const emailInput = document.getElementById('email').value.toLowerCase();
        const selectedRole = document.getElementById('role').value;

                // ROUTE TO LANDLORD DASHBOARD //        

 if (selectedRole === 'landlord') {
            
            // Map the email to one of our 3 dashboard accounts so data filters properly
            if (emailInput.includes('alpha') || emailInput === 'landlord1@example.com') {
                localStorage.setItem('ACTIVE_LANDLORD_ID', 'L1');
            } else if (emailInput.includes('vanguard') || emailInput === 'landlord2@example.com') {
                localStorage.setItem('ACTIVE_LANDLORD_ID', 'L2');
            } else if (emailInput.includes('apex') || emailInput === 'landlord3@example.com') {
                localStorage.setItem('ACTIVE_LANDLORD_ID', 'L3');
            } else {
                localStorage.setItem('ACTIVE_LANDLORD_ID', 'L1'); // Fallback test account
            }

            // Sends the landlord straight to your dashboard file
            window.location.href = '../Landlord/Dashboard.html';
        } 
        
        //  ROUTE TO STUDENT DASHBOARD //       

         else if (selectedRole === 'student') {
            // Sends the student to their folder path dashboard
            window.location.href = 'student/dashboard.html'; 
        }
    });
  </script>
