let mixertest = null;
let webrtcUp = false;

Janus.init({
    debug: "all",
    callback() {
        let janus = new Janus({
            server: "http://localhost:8088/janus",
            success: function () {
                // Attach to Audio Bridge test plugin
                janus.attach({
                    plugin: "janus.plugin.audiobridge",
                    success: function (pluginHandle) {
                        mixertest = pluginHandle;
                        Janus.log("Plugin attached! (" + mixertest.getPlugin() + ", id=" + mixertest.getId() + ")");
                        // Prepare the username registration

                        registerUsername();
                    },
                    error: function (error) {
                        alert("  -- Error attaching plugin...", error);
                        alert("Error attaching plugin... " + error);
                    },
                    // message come
                    onmessage: function (msg, jsep) {
                        Janus.log(" ::: Got a message :::");
                        Janus.log(msg);
                        Janus.log(jsep);
                        let event = msg["audiobridge"];
                        Janus.log("Event: " + event);
                        if (event != undefined && event != null) {
                            if (event === "joined") {
                                // Successfully joined, negotiate WebRTC now
                                let myid = msg["id"];
                                Janus.log("Successfully joined room " + msg["room"] + " with ID " + myid);
                                if (!webrtcUp) {
                                    webrtcUp = true;
                                    // Publish our stream
                                    Janus.log("+++++++++++Publish our stream++++++++");
                                    mixertest.createOffer(
                                        {
                                            media: {video: false},	// This is an audio only room
                                            success: function (jsep) {
                                                Janus.log("Got SDP!");
                                                Janus.log(jsep);
                                                let publish = {"request": "configure", "muted": false};
                                                mixertest.send({"message": publish, "jsep": jsep});
                                            },
                                            error: function (error) {
                                                Janus.error("WebRTC error:", error);
                                                alert("WebRTC error... " + JSON.stringify(error));
                                            }
                                        });
                                }
                                // Any room participant?
                                if (msg["participants"] !== undefined && msg["participants"] !== null) {
                                    let list = msg["participants"];
                                    Janus.log("Got a list of participants:");
                                    Janus.log(list);
                                }
                            } else if (event === "roomchanged") {
                                // The user switched to a different room
                                Janus.log("Moved to room");
                            } else if (event === "destroyed") {
                                // The room has been destroyed
                                Janus.warn("The room has been destroyed!");
                                window.location.reload();
                            } else if (event === "event") {
                                if (msg["participants"] !== undefined && msg["participants"] !== null) {
                                    Janus.log("Got a list of participants:");
                                }
                                // Any new feed to attach to?
                                if (msg["leaving"] !== undefined && msg["leaving"] !== null) {
                                    // One of the participants has gone away?
                                }
                            }
                        }

                        if (jsep !== undefined && jsep !== null) {
                            Janus.log("Handling SDP as well...");
                            Janus.log(jsep);
                            mixertest.handleRemoteJsep({jsep: jsep});
                        }
                    },

                    onlocalstream: function (stream) {
                        Janus.log(" ::: Got a local stream :::");
                        Janus.log(stream);
                        // We're not going to attach the local audio stream
                    },

                    onremotestream: function (stream) {
                        Janus.attachMediaStream($('#localVideo').get(0), stream);
                    },
                    oncleanup: function () {
                        webrtcUp = false;
                        Janus.log(" ::: Got a cleanup notification :::");
                    }

                })
            },

            error: function (error) {
                Janus.error(error);
                alert("loi");
                window.location.reload();
            },
            destroyed: function () {
                alert("destroyed");
                window.location.reload();
            }
        })
    }
});

function registerUsername() {
    let register = {
        "request": "join",
        "setup": true,
        "muted": false,
        "room": 1234,
        "display": "Duongnv3"
    };
    mixertest.send({"message": register});
}