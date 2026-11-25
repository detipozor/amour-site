// Makes the site versions of a video with the encoder built into macOS (nothing to install).
//
//   swift bin/encode-video.swift <source.mp4> <out.mp4> <width> <kbit/s>
//
//   swift bin/encode-video.swift master.mp4 assets/images/home-hero-video.mp4 1920 1000        // about 2.5 MB for 20 s
//   swift bin/encode-video.swift master.mp4 assets/images/home-hero-video-phone.mp4 1280 550   // 720p for phones
//
// HANDOFF, section 10: H.264 MP4 that starts playing before it is fully loaded, no sound track,
// no metadata from the camera (the drone writes its position into the file).

import AVFoundation
import Foundation

struct Job {
	let source: URL
	let output: URL
	let width: Int
	let kbps: Int
}

func settings(width: Int, height: Int, kbps: Int, fps: Int) -> [String: Any] {
	let compression: [String: Any] = [
		AVVideoAverageBitRateKey: kbps * 1000,
		AVVideoProfileLevelKey: AVVideoProfileLevelH264HighAutoLevel,
		AVVideoMaxKeyFrameIntervalDurationKey: 2,
		AVVideoExpectedSourceFrameRateKey: fps,
		AVVideoAllowFrameReorderingKey: true,
	]
	return [
		AVVideoCodecKey: AVVideoCodecType.h264,
		AVVideoWidthKey: width,
		AVVideoHeightKey: height,
		AVVideoScalingModeKey: AVVideoScalingModeResizeAspectFill,
		AVVideoCompressionPropertiesKey: compression,
	]
}

var passes = 0

func encode(_ job: Job) async throws {
	let asset = AVURLAsset(url: job.source)
	let tracks: [AVAssetTrack] = try await asset.loadTracks(withMediaType: .video)
	guard let track = tracks.first else {
		throw NSError(domain: "encode", code: 1, userInfo: [NSLocalizedDescriptionKey: "no video track"])
	}
	let size: CGSize = try await track.load(.naturalSize)
	let fps: Float = try await track.load(.nominalFrameRate)
	let ratio: Double = Double(size.height) / Double(size.width)
	let height: Int = Int((Double(job.width) * ratio / 2).rounded()) * 2

	let reader = try AVAssetReader(asset: asset)
	let pixels: [String: Any] = [kCVPixelBufferPixelFormatTypeKey as String: kCVPixelFormatType_420YpCbCr8BiPlanarVideoRange]
	let readerOutput = AVAssetReaderTrackOutput(track: track, outputSettings: pixels)
	readerOutput.supportsRandomAccess = true  // the second pass reads the video again
	reader.add(readerOutput)

	try? FileManager.default.removeItem(at: job.output)
	let writer = try AVAssetWriter(outputURL: job.output, fileType: .mp4)
	writer.shouldOptimizeForNetworkUse = true  // the index goes first: the video starts before it is fully loaded
	let input = AVAssetWriterInput(
		mediaType: .video,
		outputSettings: settings(width: job.width, height: height, kbps: job.kbps, fps: Int(fps.rounded()))
	)
	input.expectsMediaDataInRealTime = false
	// Two passes: the encoder first looks at the whole video, then spends the size where the picture needs it.
	input.performsMultiPassEncodingIfSupported = true
	writer.add(input)

	reader.startReading()
	writer.startWriting()
	writer.startSession(atSourceTime: .zero)

	let queue = DispatchQueue(label: "encode")
	await withCheckedContinuation { (done: CheckedContinuation<Void, Never>) in
		input.respondToEachPassDescription(on: queue) {
			guard let pass = input.currentPassDescription else {
				// No more passes: the file is ready.
				writer.finishWriting { done.resume() }
				return
			}
			if passes > 0 {
				readerOutput.reset(forReadingTimeRanges: pass.sourceTimeRanges)
			}
			passes += 1
			input.requestMediaDataWhenReady(on: queue) {
				while input.isReadyForMoreMediaData {
					guard let sample = readerOutput.copyNextSampleBuffer() else {
						input.markCurrentPassAsFinished()
						return
					}
					input.append(sample)
				}
			}
		}
	}
	print("passes: \(passes)")
	if writer.status != .completed {
		throw writer.error ?? NSError(domain: "encode", code: 2, userInfo: [NSLocalizedDescriptionKey: "writing failed"])
	}
}

let args = CommandLine.arguments
guard args.count == 5, let width = Int(args[3]), let kbps = Int(args[4]) else {
	print("usage: swift bin/encode-video.swift <source> <out.mp4> <width> <kbit/s>")
	exit(2)
}
let job = Job(source: URL(fileURLWithPath: args[1]), output: URL(fileURLWithPath: args[2]), width: width, kbps: kbps)

let finished = DispatchSemaphore(value: 0)
var failure: String?
Task {
	do {
		try await encode(job)
	} catch {
		failure = error.localizedDescription
	}
	finished.signal()
}
finished.wait()

if let failure {
	print("failed: \(failure)")
	exit(1)
}
let bytes = (try? FileManager.default.attributesOfItem(atPath: job.output.path)[.size] as? Int) ?? 0
print(String(format: "%@  %.2f MB", job.output.lastPathComponent, Double(bytes) / 1_048_576))
