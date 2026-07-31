<!DOCTYPE html>
<html>
<head>
<title>Clinician ID Generator</title>
<style>
body {
    font-family: Arial, sans-serif;
    margin: 0; /* Remove default body margin */
    background-color: #f4f4f4;

    /* Flexbox properties for centering - still needed for overall page centering */
    display: flex;
    flex-direction: column; /* Arrange children in a column */
    align-items: center;    /* Center horizontally */
    justify-content: flex-start; /* Align to the start vertically */
    min-height: 100vh; /* Ensure body takes full viewport height for vertical centering */
}

.input-group {
	font-color: grey;
    width: 450px;
    margin-bottom: 15px;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #fff;
}

.input-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.input-group input,
.input-group select {
    width: 450px; /* Adjust for padding and border */
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

/* Style for inputs with default values */
.input-group input[value]:not([value=""]) {
    color: grey;
}

/* Override for inputs that are focused or have user-entered text */
.input-group input:focus,
.input-group input:not(:placeholder-shown) {
    color: black;
}

/* NEW: Container for button and download link */
.button-and-link-container {
    display: flex; /* Use flexbox for its children */
    flex-direction: column; /* Stack them vertically */
    align-items: center; /* Center them horizontally within this container */
    margin-top: 10px; /* Space above this group */
    margin-bottom: 20px; /* Space below this group before the canvas */
}

button {
    padding: 10px 20px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    /* Removed margin-top here, it's now handled by the container's margin-top */
}

button:hover {
    background-color: #218838;
}

#outputCanvas {
    border: 1px solid black;
    display: block; /* Ensures it takes up its own line */
    background-color: #fff;
    /* margin-top is now handled by the .button-and-link-container's margin-bottom */
}

#downloadLink {
    display: none; /* Hidden by default */
    margin-top: 10px; /* Space below the button */
    padding: 10px 15px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    text-align: center;
    /* Make sure it's also a block-level element for proper spacing within its container */
}

#downloadLink:hover {
    background-color: #0056b3;
}
</style>
</head>
<body>

<h1>Clinician ID Generator</h1>
<h3>by. Dr. Van Kenneth S. Magnaye</h3>

<div class="input-group">
    <label for="baseImageInput">Upload Photo: (2:3 aspect ratio-portrait in JPG or PNG)</label>
    <input type="file" id="baseImageInput" accept="image/*" required>
</div>

<div class="input-group">
    <label for="text1Input">Student Number:</label>
    <input type="text" id="text1Input" value="" required>
</div>

<div class="input-group">
    <label for="text2Input">Last Name: (All Caps)</label>
    <input type="text" id="text2Input" value="" oninput="this.value = this.value.toUpperCase()" required>
</div>

<div class="input-group">
    <label for="text3Input">First Name:</label>
    <input type="text" id="text3Input" value="" required>
</div>

<div class="button-and-link-container">
    <button id="combineAndSave">Generate ID</button>
    <a id="downloadLink" download="combined_image.png">Download Combined Image</a>
</div>

<canvas id="outputCanvas"></canvas>


<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<script>
    document.getElementById('combineAndSave').addEventListener('click', function() {
        const text1Content = document.getElementById('text1Input').value;
        const text2Content = document.getElementById('text2Input').value.toUpperCase();
        const text3Content = document.getElementById('text3Input').value;
        const baseImageInput = document.getElementById('baseImageInput');

        if (!baseImageInput.files.length || !text1Content || !text2Content || !text3Content) {
            alert('Please fill in all required fields and upload an image.');
            return;
        }

        const canvas = document.getElementById('outputCanvas');
        const ctx = canvas.getContext('2d');
        const overlayImageSrc = 'clinic.png';

        // --- Define desired final dimensions for the base image on the canvas ---
        const finalBaseWidth = 2080;
        const finalBaseHeight = 3120;

        // --- Define desired dimensions for the overlay image on the canvas ---
        const finalOverlayWidth = 2700;
        const finalOverlayHeight = 4280;

        // --- Define position for the overlay image on the canvas ---
        const overlayX = 0;
        const overlayY = 0;

        // --- Define properties for Text 1 (fixed, but uses dynamic content) ---
        const text1Font = "bold 150px Arial";
        const text1Color = "black";
        const text1X = 1450;
        const text1Y = 2730;

        // --- Define properties for Text 2 (fixed, but uses dynamic content) ---
        const text2Font = "bold 150px Arial";
        const text2Color = "black";
        const text2X = 1200;
        const text2Y = 3045;

        // --- Define properties for Text 3 (fixed, but uses dynamic content) ---
        const text3Font = "bold 150px Arial";
        const text3Color = "black";
        const text3X = 1050;
        const text3Y = 3260;
        // Note: ctx.textAlign for text3 will be set inside checkAllImagesLoaded before drawing it

        // --- Barcode Properties ---
        const barcodeContent = text1Content; // Use text1 as the barcode content
        const barcodeType = "CODE128"; // Common types: CODE128, EAN13, UPC, etc.
        const barcodeWidth = 18; // Width of a single bar module
        const barcodeHeight = 450; // Height of the barcode
        const barcodeDisplayValue = false; // Set to true to show the number below the barcode
        const barcodeMargin = 0; // Margin around the barcode

        // Position for the barcode on the main canvas
        const barcodeX = 670; // Adjust as needed
        const barcodeY = 3805; // Adjust as needed

        // Create new Image objects to load the images
        const baseImage = new Image();
        const overlayImage = new Image();
        overlayImage.src = overlayImageSrc; // Set the fixed source

        let itemsLoadedCount = 0;
        // We need to load baseImage, overlayImage, AND generate the barcode.
        // We'll treat barcode generation as an async "load" operation for simplicity in the counter.
        const totalItemsToLoad = 3;

        // Create a temporary canvas for barcode generation
        const tempBarcodeCanvas = document.createElement('canvas');
        const tempBarcodeCtx = tempBarcodeCanvas.getContext('2d');

        // Function to generate barcode
        function generateBarcode() {
            try {
                JsBarcode(tempBarcodeCanvas, barcodeContent, {
                    format: barcodeType,
                    width: barcodeWidth,
                    height: barcodeHeight,
                    displayValue: barcodeDisplayValue,
                    margin: barcodeMargin,
                    // You can add more options here like lineColor, background, etc.
                });
                console.log("Barcode generated successfully.");
                itemsLoadedCount++;
                checkAllItemsLoaded(); // Check if everything is ready after barcode is generated
            } catch (error) {
                console.error('Error generating barcode:', error);
                alert('Failed to generate barcode. Please ensure the barcode content is valid for the selected type.');
                itemsLoadedCount = -Infinity; // Prevent drawing if an error occurs
            }
        }

        // Handle base image input
        const file = baseImageInput.files[0]; // Access the first file
        const reader = new FileReader();
        reader.onload = function(e) {
            baseImage.src = e.target.result;
        };
        reader.onerror = () => {
            console.error('Error reading base image file.');
            alert('Failed to read the base image file.');
            itemsLoadedCount = -Infinity;
        };
        reader.readAsDataURL(file);

        function checkAllItemsLoaded() {
            if (itemsLoadedCount === totalItemsToLoad) {
                // All images loaded and barcode generated, now perform drawing operations

                canvas.width = 2700;
                canvas.height = 4280;

                ctx.drawImage(baseImage, 0, 0, finalBaseWidth, finalBaseHeight);
                ctx.drawImage(overlayImage, overlayX, overlayY, finalOverlayWidth, finalOverlayHeight);
                ctx.drawImage(tempBarcodeCanvas, barcodeX, barcodeY, tempBarcodeCanvas.width, tempBarcodeCanvas.height);

                ctx.font = text1Font;
                ctx.fillStyle = text1Color;
                ctx.textAlign = "left";
                ctx.fillText(text1Content, text1X, text1Y);

                ctx.font = text2Font;
                ctx.fillStyle = text2Color;
                ctx.textAlign = "left";
                ctx.fillText(text2Content, text2X, text2Y);

                ctx.font = text3Font;
                ctx.fillStyle = text3Color;
                ctx.textAlign = "left";
                ctx.fillText(text3Content, text3X, text3Y);

                const dataURL = canvas.toDataURL('image/png');
                const downloadLink = document.getElementById('downloadLink');
                downloadLink.href = dataURL;
                downloadLink.download = `${text1Content}.png`; // Dynamic filename
                downloadLink.style.display = 'block'; // Make it visible
                downloadLink.textContent = `Download ID (${text1Content}.png)`;

                alert('ID generated! Click the download link below to save.');
            }
        }

        // Attach onload and onerror handlers for both images
        baseImage.onload = () => {
            itemsLoadedCount++;
            checkAllItemsLoaded();
        };
        baseImage.onerror = () => {
            console.error('Error loading base image (from input).');
            alert('Failed to load the selected base image. Please check the file.');
            itemsLoadedCount = -Infinity;
        };

        overlayImage.onload = () => {
            itemsLoadedCount++;
            checkAllItemsLoaded();
        };
        overlayImage.onerror = () => {
            console.error('Error loading overlay image from:', overlayImage.src);
            alert(`Failed to load overlay image (${overlayImage.src}). Please ensure it exists in the same directory.`);
            itemsLoadedCount = -Infinity;
        };

        // Start barcode generation immediately
        generateBarcode();
    });
</script>
</body>
</html>
