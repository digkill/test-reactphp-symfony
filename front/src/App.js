import React from 'react';
import logo from './logo.svg';
import './App.css';
import axios from 'axios'

class App extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            title: '',
            body: ''
        }
    }

    render() {
        return (
            <div className="App">
                <form id="contact-form" onSubmit={this.handleSubmit.bind(this)} method="POST">
                    <div className="form-group">
                        <label htmlFor="name">Title</label>
                        <input type="text" className="form-control" value={this.state.title}
                               onChange={this.onNameChange.bind(this)}/>
                    </div>

                    <div className="form-group">
                        <label htmlFor="message">Message</label>
                        <textarea className="form-control" rows="5" value={this.state.body}
                                  onChange={this.onMessageChange.bind(this)}/>
                    </div>
                    <button type="submit" className="btn btn-primary">Send</button>
                </form>
            </div>
        );
    }

    onNameChange(event) {
        this.setState({title: event.target.value})
    }

    onMessageChange(event) {
        this.setState({body: event.target.value})
    }

    handleSubmit(e) {
        e.preventDefault();

        axios({
            method: "POST",
            url: "http://127.0.0.1:8081/api/message/send",
            data: this.state
        }).then((response) => {
            if (response.data.status === 'success') {
                alert("Message Sent.");
                this.resetForm()
            } else if (response.data.status === 'fail') {
                alert("Message failed to send.")
            }
        })

    }

    resetForm() {
        this.setState({name: '', email: '', message: ''})
    }
}

export default App;