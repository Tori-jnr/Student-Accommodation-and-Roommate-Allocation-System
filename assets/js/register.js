 <script>
    function handleRegistrationRouting(event) {
        // Freeze browser page defaults immediately to check custom logic parameters first
        event.preventDefault(); 
        event.stopPropagation();
        
        // 1. GATHER CAPTURED PARAMETERS
        const emailInput = document.getElementById('email') ? document.getElementById('email').value.toLowerCase() : '';
        const roleSelector = document.getElementById('role');
        const selectedRole = roleSelector ? roleSelector.value.toLowerCase() : 'student'; 

        alert("Account validation initialized successfully. Synchronizing user credentials profile...");

        // ========================================================
        // PATH ROUTE A: AUTHORIZED USER IS A LANDLORD NODE
        // ========================================================
        if (selectedRole === 'landlord') {
            
            // Seed a baseline testing ID token inside local memory to map the correct active landlord layout filter index
            if (emailInput.includes('alpha')) {
                localStorage.setItem('ACTIVE_LANDLORD_ID', 'L1');
            } else if (emailInput.includes('vanguard')) {
                localStorage.setItem('ACTIVE_LANDLORD_ID', 'L2');
            } else if (emailInput.includes('apex')) {
                localStorage.setItem('ACTIVE_LANDLORD_ID', 'L3');
            } else {
                localStorage.setItem('ACTIVE_LANDLORD_ID', 'L1'); // Demo fallback account placeholder
            }

            // Step backward out of authentication subfolder, then forward into Landlord matrix
            window.location.href = '../Landlord/dashboard.html';
        } 
        
        // ========================================================
        // PATH ROUTE B: AUTHORIZED USER IS A STUDENT NODE
        // ========================================================
        else {
            // Step backward out of authentication subfolder, then forward into Student module dashboard view
            window.location.href = '../student/dashboard.html';
        }
    }
  </script>
