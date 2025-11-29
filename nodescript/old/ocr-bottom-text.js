const Tesseract = require("tesseract.js");
const fs = require("fs").promises;

async function extractText(imagePath, language = "eng") {
  try {
    // Check if file exists
    await fs.access(imagePath);

    console.log(`Processing: ${imagePath}`);

    const { data } = await Tesseract.recognize(imagePath, language, {
      logger: (progress) => {
        if (progress.status === "recognizing text") {
          process.stdout.write(
            `\rProgress: ${Math.round(progress.progress * 100)}%`
          );
        }
      },
    });

    console.log("\n\nExtraction complete!");
    console.log("=".repeat(60));
    console.log("Confidence:", data.confidence);
    console.log("Text length:", data.text.length);
    console.log("=".repeat(60));
    console.log(data.text);

    return data;
  } catch (error) {
    if (error.code === "ENOENT") {
      console.error("Error: File not found");
    } else {
      console.error("OCR Error:", error.message);
    }
  }
}

// Command line usage
// const args = process.argv.slice(2);

extractText("333_BACKGROUND.jpg", "eng");
