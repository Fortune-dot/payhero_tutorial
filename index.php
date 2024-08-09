<!DOCTYPE html>
<html lang="en" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STK Push Payment Form</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@3.5.0/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.3/dist/confetti.browser.min.js"></script>
</head>

<body class="bg-gray-100 flex justify-center items-center min-h-screen">
    <div class="w-full max-w-md p-8 bg-white rounded-lg shadow-lg">
        <h2 class="text-2xl font-bold mb-6 text-center">STK Push Payment</h2>
        <form id="stkPushForm" class="space-y-4">
            <div class="form-control">
                <label class="label" for="amount">
                    <span class="label-text">Amount</span>
                </label>
                <input type="number" id="amount" name="amount" placeholder="Enter amount" class="input input-bordered" required>
            </div>
            <div class="form-control">
                <label class="label" for="phone">
                    <span class="label-text">Phone Number</span>
                </label>
                <input type="tel" id="phone" name="phone" placeholder="Enter phone number" class="input input-bordered" required>
            </div>
            <button type="submit" class="btn btn-primary w-full">Initiate Payment</button>
        </form>
        <div id="notification" class="mt-4 p-4 rounded-lg hidden"></div>
        <div id="paymentStatus" class="mt-4 p-4 rounded-lg hidden"></div>
    </div>

    <script>
        const form = document.getElementById('stkPushForm');
        const notification = document.getElementById('notification');
        const paymentStatus = document.getElementById('paymentStatus');

        async function initiatePayment(amount, phone) {
            const response = await fetch('initiate_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `amount=${amount}&phone_number=${phone}`
            });
            const data = await response.json();
            console.log("Initiate Payment Response:", data); // Debugging line
            if (!data.success) {
                throw new Error(data.message + (data.response ? `: ${data.response}` : ''));
            }
            return data;
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const amount = document.getElementById('amount').value;
            const phone = document.getElementById('phone').value;

            try {
                showNotification('Initiating STK push...', 'info');

                const response = await initiatePayment(amount, phone);

                showNotification('STK push initiated successfully!', 'success');
                const externalReference = response.data.external_reference || response.data.reference; // Adjust to use reference if external_reference is missing
                if (externalReference) {
                    pollPaymentStatus(externalReference);
                } else {
                    showNotification('Error: External reference not received', 'error');
                }
            } catch (error) {
                showNotification('Error initiating STK push: ' + error.message, 'error');
            }
        });

        async function getPaymentStatus(externalReference) {
            const response = await fetch(`get_payment_status.php?external_reference=${externalReference}`);
            const data = await response.json();
            return data;
        }

        function pollPaymentStatus(externalReference) {
            const pollInterval = setInterval(async () => {
                const statusResponse = await getPaymentStatus(externalReference);
                console.log("Payment Status Response:", statusResponse); // Debugging line
                if (statusResponse.success) {
                    const payment = statusResponse.payment;
                    if (payment.status !== 'PENDING') {
                        clearInterval(pollInterval);
                        if (payment.mpesa_receipt_number) { // Check if complete data is available
                            updatePaymentStatus(payment);
                        } else {
                            console.warn('Incomplete payment data received, waiting for full update...');
                        }
                    }
                }
            }, 5000); // Poll every 5 seconds
        }

        function updatePaymentStatus(data) {
            if (data.status === 'SUCCESS') {
                // Hide the STK push initiation notification
                notification.classList.add('fade-out');
                triggerConfetti(); // Trigger confetti animation
            }

            const statusHtml = `
        <h3 class="font-bold ${data.status === 'SUCCESS' ? 'text-green-700' : 'text-red-700'}">${data.status}</h3>
        <p>Amount: ${data.amount}</p>
        <p>Receipt Number: ${data.mpesa_receipt_number}</p>
        <p>Phone: ${data.phone_number}</p>
        <p>Result: ${data.result_desc}</p>
    `;
            paymentStatus.innerHTML = statusHtml;
            paymentStatus.className = `mt-4 p-4 rounded-lg ${data.status === 'SUCCESS' ? 'bg-green-100' : 'bg-red-100'}`;
            paymentStatus.classList.add('fade-in');
            paymentStatus.classList.remove('hidden');
        }

        function showNotification(message, type) {
            notification.textContent = message;
            notification.className = `mt-4 p-4 rounded-lg ${type === 'error' ? 'bg-red-100 text-red-700' : type === 'success' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'}`;
            notification.classList.remove('hidden');
        }

        // Confetti animation
        var count = 200;
        var defaults = {
            origin: {
                y: 0.7
            }
        };

        function fire(particleRatio, opts) {
            confetti({
                ...defaults,
                ...opts,
                particleCount: Math.floor(count * particleRatio)
            });
        }

        function triggerConfetti() {
            fire(0.25, {
                spread: 26,
                startVelocity: 55,
            });
            fire(0.2, {
                spread: 60,
            });
            fire(0.35, {
                spread: 100,
                decay: 0.91,
                scalar: 0.8
            });
            fire(0.1, {
                spread: 120,
                startVelocity: 25,
                decay: 0.92,
                scalar: 1.2
            });
            fire(0.1, {
                spread: 120,
                startVelocity: 45,
            });
        }
    </script>
</body>

</html>