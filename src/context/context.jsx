import { createContext, useState } from "react";
import run from "../api/api";

export const Context = createContext();

const ContextProvider = (props) => {
  const [Input, setInput] = useState("");
  const [Result, setResult] = useState("");
  const [ShowResult, setShowResult] = useState(false);

  const onsent = async () => {
    setShowResult(false);
    setResult("");

    try {
      const response = await run(`write a blog on ${Input}`);
      let responseArray = response.split("**");
      let newResponse = "";
      for (let i = 0; i < responseArray.length; i++) {
        if (i === 0 || i % 2 !== 1) {
          newResponse += responseArray[i];
        } else {
          newResponse += "<b>" + responseArray[i] + "</b>";
        }
      }

      newResponse = newResponse.replace(/^##\s*/, "");

      let fianlResponse = newResponse.split("*").join("</br>");
      setResult(fianlResponse);
      setShowResult(true);
    } catch (error) {
      console.error("Error fetching data:", error);
      setResult("An error occurred. Please try again.");
      setShowResult(true);
    }
    setInput("");
  };

  const ContextValue = {
    onsent,
    Input,
    setInput,
    Result,
    setResult,
    ShowResult,
    setShowResult,
  };

  return (
    <Context.Provider value={ContextValue}>{props.children}</Context.Provider>
  );
};

export default ContextProvider;
