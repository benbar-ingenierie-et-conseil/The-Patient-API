const ftp = require("basic-ftp")
const path = require("path")
const fs = require("fs")
require("dotenv").config({ path: path.resolve(__dirname, "../.env") })

const exclude = new Set([
    "node_modules",
    "package-lock.json",
    "package.json",
    ".git",
    ".gitignore",
    "0x01-node",
])

async function uploadDirectory(client, localDir, remoteDir) {
    await client.ensureDir(remoteDir)

    const files = fs.readdirSync(localDir)
    for (const file of files) {
        if (exclude.has(file)) {
            console.log(`Skipping excluded: ${file}`)
            continue
        }

        const localPath = path.join(localDir, file)
        const remotePath = remoteDir + "/" + file
        const stats = fs.statSync(localPath)

        if (stats.isDirectory()) {
            await uploadDirectory(client, localPath, remotePath)
        } else {
            console.log(`Uploading ${localPath} to ${remotePath}`)
            await client.uploadFrom(localPath, remotePath)
        }
    }
}

async function main() {
    const client = new ftp.Client()
    client.ftp.verbose = true
    try {
        await client.access({
            host: process.env.FTP_HOST,
            user: process.env.FTP_USER,
            password: process.env.FTP_PASS,
            secure: false
        })

        const localDir = path.resolve(__dirname, "..")
        const remoteDir = process.env.FTP_REMOTE_DIR
        await uploadDirectory(client, localDir, remoteDir)
        console.log("✅ Upload completed!")
    }
    catch(err) {
        console.error("❌ FTP Upload failed:", err)
    }
    client.close()
}

main()
