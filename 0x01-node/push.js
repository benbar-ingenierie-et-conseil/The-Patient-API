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

async function syncDirectory(client, localDir, remoteDir) {
    await client.ensureDir(remoteDir)
    const remoteFiles = await client.list(remoteDir)
    const localFiles = fs.readdirSync(localDir)

    const localSet = new Set(localFiles)

    for (const file of remoteFiles) {
        if (exclude.has(file.name)) continue
        const localPath = path.join(localDir, file.name)
        if (!localSet.has(file.name)) {
            const remotePath = remoteDir + "/" + file.name
            console.log(`🗑️ Deleting ${remotePath} (not found locally)`)
            if (file.isDirectory) {
                await client.removeDir(remotePath)
            } else {
                await client.remove(remotePath)
            }
        }
    }

    for (const file of localFiles) {
        if (exclude.has(file)) {
            console.log(`⏭️ Skipping excluded: ${file}`)
            continue
        }

        const localPath = path.join(localDir, file)
        const remotePath = remoteDir + "/" + file
        const stats = fs.statSync(localPath)

        if (stats.isDirectory()) {
            await syncDirectory(client, localPath, remotePath)
        } else {
            console.log(`⬆️ Uploading ${localPath} → ${remotePath}`)
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
        await syncDirectory(client, localDir, remoteDir)
        console.log("✅ Sync completed!")
    } catch (err) {
        console.error("❌ FTP sync failed:", err)
    }
    client.close()
}

main()
